Here you can see a complete example of how the package can be used to do CRUD operations with users
```html
@extends('layouts.app')
@section('body')
    <div class="card card-body bg-secondary-subtle">
        <button data-bs-target="#add-user-modal" data-bs-toggle="modal" class="btn btn-success btn-block">Erstellen</button>
        <x-sleek::modal-form
            title="User Erstellen"
            :action="route('users.store')"
            method="POST"
            id="add-user-modal"
        >
            <x-sleek::form-field name="name" />
            <x-sleek::form-field :label="__('users.fields.email')" name="email" />
        </x-sleek::modal-form>

        <br>

        <x-sleek::entity-table
            :entities="$users"
            :columns="['name','email','actions']"
        >
            <x-slot:column-actions bind="$_,$user">
                <div class="d-flex gap-2">
                    <x-sleek::form method="DELETE" action="{{route('users.destroy',compact('user'))}}">
                        <x-sleek::form-actions />
                    </x-sleek::form>
                    <button data-bs-target="#edit-user-{{$user->id}}" data-bs-toggle="modal" class="btn btn-success btn-block">Ändern</button>
                </div>

                <x-sleek::modal-form
                    title="User Erstellen"
                    :action="route('users.update', compact('user'))"
                    method="PUT"
                    id="edit-user-{{$user->id}}"
                    formType="entity-form"
                    :model="$user"
                    :fields="['name','email']"
                >
                    <x-slot:submit :label="__('common.actions.update')"></x-slot:submit>
                </x-sleek::modal-form>
            </x-slot:column-actions>
        </x-sleek::entity-table>
    </div>
@endsection
```
