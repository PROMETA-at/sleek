<?php

namespace Tests\Unit;

use Prometa\Sleek\Pagination\LengthAwarePaginator;
use Prometa\Sleek\Pagination\UrlWindow;
use Tests\TestCase;

/**
 * The default sleek paginator uses windowSize = 3 (Laravel's onEachSide) and
 * borderWindowSize = 2. Under these defaults, with totalPages = 15:
 *
 *   - Border case applies when current <= W + B = 5 (or current > 15 - 5 = 10).
 *   - In the border case the slider expands so the rendered range from the edge
 *     spans 2*W + B = 8 pages — keeping element count visually balanced with
 *     the middle case (which renders 2 + 7 + 2 = 11 page links).
 *
 * Tests below pin both the default behavior and customization via onEachSide /
 * borderWindowSize.
 */
class UrlWindowTest extends TestCase
{
    private function paginator(int $totalPages, int $currentPage): LengthAwarePaginator
    {
        $perPage = 10;
        $total = $totalPages * $perPage;

        return new LengthAwarePaginator(
            items: array_fill(0, $perPage, 'item'),
            total: $total,
            perPage: $perPage,
            currentPage: $currentPage,
            options: ['path' => '/list'],
        );
    }

    /**
     * Reduce the elements-shape down to the page numbers and ellipsis markers
     * so assertions are about the window, not URL strings.
     */
    private function shape(array $elements): array
    {
        return array_map(
            fn ($el) => is_array($el) ? array_keys($el) : $el,
            array_values($elements),
        );
    }

    public function test_left_border_case_expands_slider_to_2W_plus_B(): void
    {
        // current=1 of 15, W=3, B=2 → keep {1..8} ∪ {14, 15}.
        $elements = UrlWindow::make($this->paginator(totalPages: 15, currentPage: 1));

        $this->assertSame(
            [[1, 2, 3, 4, 5, 6, 7, 8], '...', [14, 15]],
            $this->shape($elements),
        );
    }

    public function test_middle_case_renders_borders_around_slider(): void
    {
        // current=8 of 15, W=3, B=2 → keep {1,2} ∪ {5..11} ∪ {14,15}.
        $elements = UrlWindow::make($this->paginator(totalPages: 15, currentPage: 8));

        $this->assertSame(
            [[1, 2], '...', [5, 6, 7, 8, 9, 10, 11], '...', [14, 15]],
            $this->shape($elements),
        );
    }

    public function test_right_border_case_is_symmetric(): void
    {
        // current=15 of 15, W=3, B=2 → keep {1,2} ∪ {8..15}.
        $elements = UrlWindow::make($this->paginator(totalPages: 15, currentPage: 15));

        $this->assertSame(
            [[1, 2], '...', [8, 9, 10, 11, 12, 13, 14, 15]],
            $this->shape($elements),
        );
    }

    public function test_smooth_transition_at_border_threshold(): void
    {
        // current=5 of 15: border case (5 <= W+B=5) → 1..8 ∪ 14..15.
        $atThreshold = UrlWindow::make($this->paginator(totalPages: 15, currentPage: 5));
        $this->assertSame(
            [[1, 2, 3, 4, 5, 6, 7, 8], '...', [14, 15]],
            $this->shape($atThreshold),
        );

        // current=6 of 15: middle case, but slider 3..9 merges with left border
        // {1,2} into a single contiguous run — no ellipsis on the left.
        $afterThreshold = UrlWindow::make($this->paginator(totalPages: 15, currentPage: 6));
        $this->assertSame(
            [[1, 2, 3, 4, 5, 6, 7, 8, 9], '...', [14, 15]],
            $this->shape($afterThreshold),
        );
    }

    public function test_small_paginator_collapses_to_single_range(): void
    {
        // last=4, current=3 with default W=3, B=2: keep covers all pages.
        $elements = UrlWindow::make($this->paginator(totalPages: 4, currentPage: 3));

        $this->assertSame([[1, 2, 3, 4]], $this->shape($elements));
    }

    public function test_single_page_returns_empty(): void
    {
        $elements = UrlWindow::make($this->paginator(totalPages: 1, currentPage: 1));

        $this->assertSame([], $this->shape($elements));
    }

    public function test_custom_window_and_border_in_middle_case(): void
    {
        // W=1, B=1, last=15, current=4: threshold W+B=2; 4 > 2 → middle.
        // Slider 3..5; borders {1}, {15}.
        $paginator = $this->paginator(totalPages: 15, currentPage: 4)
            ->onEachSide(1)
            ->borderWindowSize(1);

        $elements = UrlWindow::make($paginator);

        $this->assertSame(
            [[1], '...', [3, 4, 5], '...', [15]],
            $this->shape($elements),
        );
    }

    public function test_custom_window_at_left_border(): void
    {
        // W=1, B=1, last=15, current=1: border case → 1..(2W+B)=1..3 ∪ {15}.
        $paginator = $this->paginator(totalPages: 15, currentPage: 1)
            ->onEachSide(1)
            ->borderWindowSize(1);

        $elements = UrlWindow::make($paginator);

        $this->assertSame(
            [[1, 2, 3], '...', [15]],
            $this->shape($elements),
        );
    }

    public function test_border_window_size_setter_returns_paginator_for_chaining(): void
    {
        $paginator = $this->paginator(totalPages: 15, currentPage: 1);

        $this->assertSame($paginator, $paginator->borderWindowSize(1));
        $this->assertSame(1, $paginator->borderWindowSize);
    }
}
