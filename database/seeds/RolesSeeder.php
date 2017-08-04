<?php

use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('abilities')->truncate();
        DB::table('assigned_roles')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        //Bouncer::allow(\Auth::user())->to('ban-users');
        //Bouncer::allow('admin')->to('ban-users');
        //Bouncer::assign('admin')->to(\Auth::user());

        Bouncer::allow('superadmin')->to('view-users'); // sa only
        Bouncer::allow('superadmin')->to('edit-users'); // sa only
        Bouncer::allow('superadmin')->to('ban-users'); // sa only

        Bouncer::allow('superadmin')->to('admin-pages'); // sa/a only
        Bouncer::allow('superadmin')->to('confirm-pages');
        Bouncer::allow('superadmin')->to('create-pages');
        Bouncer::allow('superadmin')->to('edit-pages');
        Bouncer::allow('superadmin')->to('delete-pages');

        Bouncer::allow('superadmin')->to('admin-venues');
        Bouncer::allow('superadmin')->to('confirm-venues');
        Bouncer::allow('superadmin')->to('create-venues');
        Bouncer::allow('superadmin')->to('edit-venues');
        Bouncer::allow('superadmin')->to('delete-venues');

        Bouncer::allow('superadmin')->to('admin-events');
        Bouncer::allow('superadmin')->to('confirm-events');
        Bouncer::allow('superadmin')->to('create-events');
        Bouncer::allow('superadmin')->to('edit-events');
        Bouncer::allow('superadmin')->to('delete-events');

        Bouncer::allow('admin')->to('view-users');
        Bouncer::allow('admin')->to('edit-users');

        Bouncer::allow('admin')->to('admin-pages'); // sa/a only
        Bouncer::allow('admin')->to('confirm-pages');
        Bouncer::allow('admin')->to('create-pages');
        Bouncer::allow('admin')->to('edit-pages');
        Bouncer::allow('admin')->to('delete-pages');

        Bouncer::allow('admin')->to('admin-venues');
        Bouncer::allow('admin')->to('confirm-venues');
        Bouncer::allow('admin')->to('create-venues');
        Bouncer::allow('admin')->to('edit-venues');
        Bouncer::allow('admin')->to('delete-venues');

        Bouncer::allow('admin')->to('admin-events');
        Bouncer::allow('admin')->to('confirm-events');
        Bouncer::allow('admin')->to('create-events');
        Bouncer::allow('admin')->to('edit-events');
        Bouncer::allow('admin')->to('delete-events');

        Bouncer::allow('mastereditor')->to('confirm-pages');
        Bouncer::allow('mastereditor')->to('create-pages');
        Bouncer::allow('mastereditor')->to('edit-pages');
        Bouncer::allow('mastereditor')->to('delete-pages');

        Bouncer::allow('mastereditor')->to('confirm-venues');
        Bouncer::allow('mastereditor')->to('create-venues');
        Bouncer::allow('mastereditor')->to('edit-venues');
        Bouncer::allow('mastereditor')->to('delete-venues');

        Bouncer::allow('mastereditor')->to('confirm-venues');
        Bouncer::allow('mastereditor')->to('create-events');
        Bouncer::allow('mastereditor')->to('edit-events');
        Bouncer::allow('mastereditor')->to('delete-events');

        Bouncer::allow('contributor')->to('create-pages');
        Bouncer::allow('contributor')->to('create-venues');
        Bouncer::allow('contributor')->to('create-events');
        //contributers will be able to edit and delete their own pages/events/venues once created

    }
}
