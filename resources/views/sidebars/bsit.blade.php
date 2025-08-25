<flux:navlist.item icon="home" :href="route('dashboard')" wire:navigate.prefetch>
    Dashboard
</flux:navlist.item>
<flux:navlist.item icon="plus" :href="route('ppmp.create')" wire:navigate.prefetch>
    Add Procurement Plan
</flux:navlist.item>
<flux:navlist.item icon="pencil" :href="route('ppmp.manage')" wire:navigate>
    Manage 
</flux:navlist.item>
<flux:navlist.item icon="arrow-right" :href="route('ppmp.view')" wire:navigate>
    Submit 
</flux:navlist.item>
<flux:navlist.item icon="table-cells" :href="route('ppmp.bsit')" wire:navigate>
    Recent Project Plan
</flux:navlist.item>