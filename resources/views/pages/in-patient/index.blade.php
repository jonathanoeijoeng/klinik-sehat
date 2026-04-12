<?php

use Livewire\Component;

new class extends Component {
    public function create()
    {
        return redirect()->route('appointments.index');
    }
};
?>

<div>
    <x-header header="Rawat Jalan" description="" />
    <x-button wire:click="create" class="mb-4" color="brand">Registrasi</x-button>

</div>
