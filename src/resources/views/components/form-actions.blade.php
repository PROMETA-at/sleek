@aware(['method','action'])
@props(['show' => ['submit']])
@php
    if (!isset($cancel)) $cancel = new \Illuminate\View\ComponentSlot();
    if (!isset($submit)) $submit = new \Illuminate\View\ComponentSlot();
    if (!isset($reset)) $reset = new \Illuminate\View\ComponentSlot();
@endphp
<div {{$attributes}}>
    @if(in_array('reset', $show))
        <button class="btn btn-outline-warning" type="button" x-on:dblclick="$el.closest('form').reset()" title="Double Click to perform">
            {{ $reset->attributes->get('label') ?? __('common.actions.reset') }}
        </button>
    @endif

    @if(in_array('cancel', $show))
        <a href="{{url()->previous()}}" {{ $cancel->attributes->except(['label'])->class(['btn', 'btn-outline-secondary']) }}>
            {{ $cancel->attributes->get('label') ?? __('common.actions.cancel') }}
        </a>
    @endif

    @if(in_array('submit', $show))
        @if($method === 'POST')
            <button type="submit" class="btn btn-success" :disabled="loading">
                <span x-show="loading" class="spinner-border spinner-border-sm form-spinner" role="status" aria-hidden="true"></span>
                {{ $submit->attributes->get('label') ?? __('common.actions.submit') }}
            </button>
        @elseif($method === 'PUT')
            <button type="submit" class="btn btn-success" :disabled="loading">
                <span x-show="loading" class="spinner-border spinner-border-sm form-spinner" role="status" aria-hidden="true"></span>
                {{ $submit->attributes->get('label') ?? __('common.actions.update') }}
            </button>
        @elseif($method === 'DELETE')
            @php($id = 'sleek-modal-' . \Illuminate\Support\Str::random(16))
            <button type="button" data-bs-target="#{{$id}}" data-bs-toggle="modal" class="btn btn-danger">
                {{ $submit->attributes->get('label') ?? __('common.actions.delete') }}
            </button>
            <div class="modal fade" id="{{$id}}" tabindex="-1" aria-labelledby="confirmModal" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">{{__('common.confirm.title')}}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center" style="padding-top: 0px;">
                            <i class="bi bi-exclamation-octagon" style="color:#ff4141; font-size: 4rem; text-shadow: 2px 2px 4px #a0aec0;"></i>
                            <div class="mt-2 fs-5">{{__('common.confirm.text')}}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{__('common.confirm.close')}}</button>
                            <button type="submit" class="btn btn-success" :disabled="loading">
                                <span x-show="loading" class="spinner-border spinner-border-sm form-spinner" role="status" aria-hidden="true"></span>
                                {{ __('common.confirm.ok') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <button type="submit" class="btn btn-success" :disabled="loading">
                <span x-show="loading" class="spinner-border spinner-border-sm form-spinner" role="status" aria-hidden="true"></span>
                {{ $submit->attributes->get('label') ?? __('common.actions.submit') }}
            </button>
        @endif
    @endif
</div>
<style>
    .hidden-spinner{
        display: none;
    }
</style>
@once
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            if (typeof Alpine === 'undefined') {
                console.log('Alpine.js is not loaded.');
                document.querySelectorAll('.form-spinner').forEach(function(spinner) {
                    spinner.classList.add('hidden-spinner');
                });
            } else {
                console.log('Alpine.js is loaded.');
            }
        });
    </script>
@endonce

