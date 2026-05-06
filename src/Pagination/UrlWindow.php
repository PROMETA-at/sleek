<?php namespace Prometa\Sleek\Pagination;

/**
 * Builds a pagination window of the form
 *
 *     [ [1 => url, 2 => url, ...], '...', [n => url, n+1 => url, ...], '...', [last => url] ]
 *
 * Replaces Laravel's built-in window math, which bloats to `2 * onEachSide + 4`
 * pages near the edges. This implementation is parameterized by two values
 * read from the paginator:
 *
 *   - W = $paginator->onEachSide      — pages on each side of the current page
 *   - B = $paginator->borderWindowSize — pages pinned at each edge
 *
 * Behavior by case (current = c, last page = L):
 *
 *   - Border case  (c ≤ W + B  or  c > L - W - B):
 *       the slider absorbs the adjacent border and expands so the rendered
 *       run from that edge spans `2*W + B` pages. The opposite border is
 *       still pinned. Keeps the rendered button count roughly constant
 *       across pages.
 *
 *   - Middle case  (otherwise):
 *       keep `{1..B}`, the slider `{c-W..c+W}`, and `{L-B+1..L}`. Adjacent
 *       runs are merged so an ellipsis only appears where pages are skipped.
 */
class UrlWindow
{
    public function __construct(protected LengthAwarePaginator $paginator) {}

    public static function make(LengthAwarePaginator $paginator): array
    {
        return (new self($paginator))->get();
    }

    public function get(): array
    {
        $lastPage = $this->paginator->lastPage();

        if ($lastPage <= 1) {
            return [];
        }

        $ranges = $this->collapseIntoRanges($this->keptPages($lastPage));

        return $this->joinWithEllipses($ranges);
    }

    /**
     * @return list<int> Sorted, deduped, in-range page numbers.
     */
    private function keptPages(int $lastPage): array
    {
        $current = $this->paginator->currentPage();
        $W = $this->paginator->onEachSide;
        $B = $this->paginator->borderWindowSize;

        if ($current <= $W + $B) {
            // Left border case: slider absorbs the left border and extends.
            $kept = array_merge(
                $this->range(1, 2 * $W + $B),
                $this->range($lastPage - $B + 1, $lastPage),
            );
        } elseif ($current > $lastPage - $W - $B) {
            // Right border case: symmetric.
            $kept = array_merge(
                $this->range(1, $B),
                $this->range($lastPage - 2 * $W - $B + 1, $lastPage),
            );
        } else {
            // Middle case.
            $kept = array_merge(
                $this->range(1, $B),
                $this->range($current - $W, $current + $W),
                $this->range($lastPage - $B + 1, $lastPage),
            );
        }

        $kept = array_filter($kept, fn ($p) => $p >= 1 && $p <= $lastPage);
        $kept = array_values(array_unique($kept));
        sort($kept);

        return $kept;
    }

    /**
     * @return list<int>
     */
    private function range(int $start, int $end): array
    {
        return $start <= $end ? range($start, $end) : [];
    }

    /**
     * Group consecutive integers into [start, end] ranges.
     *
     * @param  list<int>  $pages
     * @return list<array{0: int, 1: int}>
     */
    private function collapseIntoRanges(array $pages): array
    {
        $ranges = [];
        foreach ($pages as $page) {
            $last = count($ranges) - 1;
            if ($last >= 0 && $ranges[$last][1] + 1 === $page) {
                $ranges[$last][1] = $page;
            } else {
                $ranges[] = [$page, $page];
            }
        }

        return $ranges;
    }

    /**
     * @param  list<array{0: int, 1: int}>  $ranges
     */
    private function joinWithEllipses(array $ranges): array
    {
        $elements = [];
        foreach ($ranges as $i => [$start, $end]) {
            if ($i > 0) {
                $elements[] = '...';
            }
            $elements[] = $this->paginator->getUrlRange($start, $end);
        }

        return $elements;
    }
}
