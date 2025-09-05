<x-sleek::view :page:nav:items="[
    ['label' => 'Scratchpad', 'route' => '/scratchpad'],
]">
  <x-sleek::form>
    <x-sleek::form-field name="text-field" />
    <x-sleek::form-field name="number-field" type="number" />
    <x-sleek::form-field name="select-field" type="select">
      <option value="1">Option 1</option>
      <option value="2">Option 2</option>
      <option value="3">Option 3</option>
    </x-sleek::form-field>
    <x-sleek::form-field name="textarea-field" type="textarea" style="height: 8rem" />
  </x-sleek::form>

  <hr />

  <x-sleek::card>
    A Card
  </x-sleek::card>

  <hr />


  <section class="d-flex gap-4">
    <button class="btn btn-primary"
            {{--          data-bs-target="#modal-1" data-bs-toggle="modal" --}}
            _="on click showModal() the next <dialog />"
    >
      Modal Native
    </button>
    <x-sleek::modal native id="modal-1">
      <x-slot:header>Modal Header</x-slot:header>
      <x-sleek::form>
        <x-sleek::form-field name="text-field" />
      </x-sleek::form>
      <x-slot:footer>
        Footer
      </x-slot:footer>
    </x-sleek::modal>

    <button class="btn btn-primary"
            data-bs-target="#modal-2" data-bs-toggle="modal"
    >
      Modal Legacy-Bootstrap
    </button>
    <x-sleek::modal id="modal-2">
      <x-slot:header>Modal Header</x-slot:header>
      Modal!
      <x-slot:footer>
        Footer
      </x-slot:footer>
    </x-sleek::modal>
  </section>

  <hr />

  <h3>Icon Component</h3>
  <div class="d-flex gap-3 align-items-center">
    <!-- Basic icon -->
    <x-icon envelope /> Basic envelope
    
    <!-- Icon with class attribute -->
    <x-icon person class="text-primary" /> Primary person
    
    <!-- Icon with style attribute -->
    <x-icon house style="font-size: 2rem; color: green;" /> Large green house
    
    <!-- Icon with multiple attributes -->
    <x-icon star class="text-warning" style="font-size: 1.5rem;" title="Favorite" /> Warning star with tooltip

    <!-- Using explicit name -->
    <x-icon name="hand-thumbs-up-fill" class="text-primary" /> Danger star with explicit name
  </div>

</x-sleek::view>
