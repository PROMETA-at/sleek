<div class="row">
    @foreach($entities as $entity)
        <div class="col-lg-4 col-md-6 d-flex align-items-stretch gap-3">
            <x-sleek::card reactivity="true" class="mb-3">

                <dl class="row mb-0">
                    @foreach($columns as $column)
                        <dt class="col-sm-4">{{ $column['label'] }}</dt>
                        <dd class="col-sm-8">{{ data_get($entity, $column['accessor']) }}</dd>
                    @endforeach
                </dl>

            </x-sleek::card>
        </div>
    @endforeach
</div>
