{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Courses" icon="la la-question" :link="backpack_url('course/custom-view')" />
<x-backpack::menu-item title="Lessons" icon="la la-question" :link="backpack_url('lesson/custom-view')" />
<x-backpack::menu-item title="Resources" icon="la la-question" :link="backpack_url('resource/custom-view')" />
<x-backpack::menu-item title="Enrollments" icon="la la-question" :link="backpack_url('enrollment/custom-view')" />
<x-backpack::menu-item title="Users" icon="la la-question" :link="backpack_url('user/custom-view')" />


<x-backpack::menu-item title="Reports" icon="la la-question" :link="backpack_url('report')" />
