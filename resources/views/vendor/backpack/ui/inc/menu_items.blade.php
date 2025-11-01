{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}
    </a>
</li>


<x-backpack::menu-dropdown title="Backpack CRUD" icon="la la-bolt">
    <x-backpack::menu-dropdown-item title="Users" :link="backpack_url('user')" />
    <x-backpack::menu-dropdown-item title="Courses" :link="backpack_url('course')" />
    <x-backpack::menu-dropdown-item title="Lessons" :link="backpack_url('lesson')" />
    <x-backpack::menu-dropdown-item title="Resources" :link="backpack_url('resource')" />
    <x-backpack::menu-dropdown-item title="Enrollments" :link="backpack_url('enrollment')" />
</x-backpack::menu-dropdown>


{{-- custom AJAX CRUD dropdown --}}
<x-backpack::menu-dropdown title="Custom AJAX CRUD" icon="la la-bolt">
    <x-backpack::menu-dropdown-item title="Users" :link="backpack_url('ajax-user')" />
    <x-backpack::menu-dropdown-item title="Courses" :link="backpack_url('ajax-course')" />
    <x-backpack::menu-dropdown-item title="Lessons" :link="backpack_url('ajax-lesson')" />
    <x-backpack::menu-dropdown-item title="Resources" :link="backpack_url('ajax-resource')" />
    <x-backpack::menu-dropdown-item title="Enrollments" :link="backpack_url('ajax-enrollment')" />
</x-backpack::menu-dropdown>

<x-backpack::menu-item title="Reports" icon="la la-question" :link="backpack_url('report')" />
<x-backpack::menu-item title="OCR Scanner" icon="la la-question" :link="backpack_url('/file-uploader')" />