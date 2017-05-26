<?php
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Temporarily increase memory limit to 2048M
        //ini_set('memory_limit', '2048M');
        $this->call('CategoriesSeeder');

        $this->call('UserDataSeeder');

        $this->call('VenueDataSeeder');
        $this->call('PageDataSeeder');
        $this->call('EventDataSeeder');

        $this->call('RolesSeeder');
    }

}

class UserDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();
        $datetime    = Carbon::now();
        $lat         = '41.94';
        $lng         = '-87.67';
        $latlngval   = $lat . ', ' . $lng;
        $glypherinfo = [
            'name'        => "Shawn 'glypher' Dalton",
            'username'    => "glypher",
            'email'       => "glypher@gmail.com",
            'facebook_id' => '10109892803653991',
            'avatar'      => 'https://graph.facebook.com/v2.8/10109892803653991/picture?type=normal',
            'lat'         => $lat,
            'lng'         => $lng,
            'location'    => DB::raw("POINT($latlngval)"),
            'confirmed'   => '1',
            'slug'        => 'glypher',
            'created_at'  => $datetime,
            'updated_at'  => $datetime,
        ];
        //NOTE LOCATION is POINT (since we're not using model, but raw DB entry)

        $id   = DB::table('users')->insertGetId($glypherinfo);
        $user = User::find($id);

        //Logging in glypher so we can do all the seeding
        \Auth::login($user);
        \Auth::user()->assign('superadmin');
        $users = factory('App\Models\User', 'user', 100)->create();

    }
}

class VenueDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('venues')->delete();
        $venues = factory('App\Models\Venue', 10)->states('chicago')->create();
    }
}

class PageDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('pages')->delete();
        $pages = factory('App\Models\Page', 10)->states('chicago')->create();
    }
}

class EventDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('events')->delete();
        $events = factory('App\Models\Event', 10)->states('chicago')
            ->create()
            ->each(function ($u) {
                $u->participant()->save(factory(App\Models\Participant::class)->make());
                $u->participant()->save(factory(App\Models\Participant::class)->make());
                $u->participant()->save(factory(App\Models\Participant::class)->make());
                $u->participant()->save(factory(App\Models\Participant::class)->make());
            });
    }
}

class CategoriesSeeder extends Seeder
{
    public function run()
    {}

}

class RolesSeeder extends Seeder
{
    public function run()
    {

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
        Bouncer::allow('superadmin')->to('create-events');
        Bouncer::allow('superadmin')->to('edit-events');
        Bouncer::allow('superadmin')->to('delete-events');

        Bouncer::allow('admin')->to('admin-pages'); // sa/a only
        Bouncer::allow('admin')->to('confirm-pages');
        Bouncer::allow('admin')->to('view-users');
        Bouncer::allow('admin')->to('edit-users');
        Bouncer::allow('admin')->to('create-pages');
        Bouncer::allow('admin')->to('edit-pages');
        Bouncer::allow('admin')->to('delete-pages');
        Bouncer::allow('admin')->to('admin-venues');
        Bouncer::allow('admin')->to('confirm-venues');
        Bouncer::allow('admin')->to('create-venues');
        Bouncer::allow('admin')->to('edit-venues');
        Bouncer::allow('admin')->to('delete-venues');
        Bouncer::allow('admin')->to('create-events');
        Bouncer::allow('admin')->to('edit-events');
        Bouncer::allow('admin')->to('delete-events');

        Bouncer::allow('mastereditor')->to('confirm-pages');
        Bouncer::allow('mastereditor')->to('create-pages');
        Bouncer::allow('mastereditor')->to('edit-pages');
        Bouncer::allow('mastereditor')->to('delete-pages');
        Bouncer::allow('mastereditor')->to('admin-venues');
        Bouncer::allow('mastereditor')->to('confirm-venues');
        Bouncer::allow('mastereditor')->to('create-venues');
        Bouncer::allow('mastereditor')->to('edit-venues');
        Bouncer::allow('mastereditor')->to('delete-venues');
        Bouncer::allow('mastereditor')->to('create-events');
        Bouncer::allow('mastereditor')->to('edit-events');
        Bouncer::allow('mastereditor')->to('delete-events');

    }

}
