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

        $this->call('UserDataSeeder');

        $this->call('VenueDataSeeder');
        $this->call('PageDataSeeder');
        $this->call('ShowpageDataSeeder');

        $this->call('EventDataSeeder');

        $this->call('PageCategoriesSeeder');

        $this->call('UserLikesSeeder');

        $this->call('RolesSeeder');
    }

}

class UserDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
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
        $users = factory('App\Models\User', 'user', 20)->create();

    }
}

class VenueDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('venues')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $venues = factory('App\Models\Venue', 10)->states('chicago')->create();
    }
}

class PageDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('pages')->truncate();
        DB::table('page_eventroles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

//my seeding for the percentages (lots of poerfomers, some producers, few shows, few perfomer/producers)

        //make at least one of each page:
        $pages = factory('App\Models\Page', 1)
            ->states('chicago')
            ->create()
            ->each(function ($u) {
                $u->eventroles()->syncWithoutDetaching(1);
            });
        $pages = factory('App\Models\Page', 1)
            ->states('chicago')
            ->create()
            ->each(function ($u) {
                $u->eventroles()->syncWithoutDetaching(2);
            });
        // $pages = factory('App\Models\Page', 1)
        //     ->states('chicago')
        //     ->create()
        //     ->each(function ($u) {
        //         $u->eventroles()->syncWithoutDetaching(3);
        //     });

        $pages = factory('App\Models\Page', 50)
            ->states('chicago')
            ->create()
            ->each(function ($u) {
                $seed   = [3, 3, 3, 3, 3, 5, 5, 15];
                $godwin = Faker\Factory::create()->randomElement($array = $seed);

                if ($godwin % 3 == 0) {
                    // participant
                    $u->eventroles()->syncWithoutDetaching(1);
                }

                if ($godwin % 5 == 0) {
                    // producer
                    $u->eventroles()->syncWithoutDetaching(2);
                }

            });
    }
}

class ShowpageDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('showpages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $showpages = factory('App\Models\Showpage', 15)
            ->create();
    }
}

class EventDataSeeder extends Seeder
{
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('events')->truncate();
        DB::table('participants')->truncate();
        DB::table('producers')->truncate();
        DB::table('event_shows')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $events = factory('App\Models\Event', 200)->states('chicago')
            ->create()
            ->each(function ($u) {

                //Make some participants
                $num_participants = Faker\Factory::create()->numberBetween($min = 1, $max = 5);
                foreach (range(1, $num_participants) as $index) {
                    $u->participants()->save(factory(App\Models\Participant::class)->make());
                }

//Maybe make a producer:
                $num_producers = Faker\Factory::create()->optional($weight = 0.2)->randomElement($array = array(1, 1, 1, 2)); // 80% chance of NULL
                if ($num_producers) {
                    foreach (range(1, $num_producers) as $index) {
                        $u->producers()->save(factory(App\Models\Producer::class)->make());
                    }
                }

                //Maybe attach a show:
                $num_shows = Faker\Factory::create()->optional($weight = 0.5)->randomElement($array = array(1, 1, 1, 1, 1, 1, 1, 1, 2)); // 50% chance of NULL
                //echo ($num_shows);
                if ($num_shows) {
                    foreach (range(1, $num_shows) as $index) {
                        $u->eventshows()->save(factory(App\Models\Eventshow::class)->make());
                    }
                }

            });
    }
}

class PageCategoriesSeeder extends Seeder
{
    //php artisan db:seed --class=CategoriesSeeder
    public function run()
    {

        $pagecategories = [
            'Comedian'      => ['', 'Stand-up Comedian', 'Comedic Writer', 'Improv Comedian', 'Sketch Comedian'],
            'Comedy Group'  => ['', 'Improv Group', 'Sketch Comedy Group'],
            'Musician'      => ['', 'Drummer', 'Guitarist', 'Singer', 'Bassist', 'Pianist', 'Trumpet Player'],
            'Band'          => ['', 'Rock Band', 'Pop Band', 'Funk Band', 'Psychedelic Band', 'Emo Band', 'Pop-Punk Band', 'Acoustic Band', 'Country Band', 'Americana', 'Cover Band', 'Rock Band'],

            'Athlete'       => ['', 'Roller Derby Player', 'Baseball Player', 'Baseball Player', 'Basketball Player'],
            'Sports League' => ['', 'Basketball League', 'Rugby League', 'Roller Derby League'],
            'Sports Team'   => ['', 'Roller Derby Team', 'Basketball Team', 'Rugby Team'],
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('pagecategories')->truncate();
        DB::table('pagesubcategories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        foreach ($pagecategories as $category => $subCategories) {
            $id = \App\Models\Pagecategory::create(['name' => $category])->id;
            foreach ($subCategories as $subCategory) {
                \App\Models\Pagesubcategory::create([
                    'category_id' => $id,
                    'name'        => $subCategory,
                ]);
            }
        }
    }
}

class UserLikesSeeder extends Seeder
{
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('likeables')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $users  = App\Models\User::pluck('id')->toArray();
        $pages  = App\Models\Page::pluck('id')->toArray();
        $venues = App\Models\Venue::pluck('id')->toArray();

        foreach ($users as $user_id) {
            $num_pagelikes  = Faker\Factory::create()->numberBetween($min = 0, $max = 10);
            $num_venuelikes = Faker\Factory::create()->numberBetween($min = 0, $max = 3);

//LIKE SOME PAGES
            $this->makelikes($num_pagelikes, 'App\Models\Page', $pages, $user_id);

//LIKE SOME VENUES
            $this->makelikes($num_venuelikes, 'App\Models\Venue', $venues, $user_id);

        }

    }

    public function makelikes($num_likes, $likeable_type, $likeable_array, $user_id)
    {
        foreach (range(1, $num_likes) as $index) {

            $likeable_id = Faker\Factory::create()->randomElement($array = $likeable_array);

            $existing_like = App\Models\Like::withTrashed()->whereLikeableType($likeable_type)->whereLikeableId($likeable_id)->whereUserId($user_id)->first();

            if (is_null($existing_like)) {
                App\Models\Like::create([
                    'user_id'       => $user_id,
                    'likeable_id'   => $likeable_id,
                    'likeable_type' => $likeable_type,
                ]);
                //return 'liked';
            } else {
                if (is_null($existing_like->deleted_at)) {
                    $existing_like->delete();
                    //return 'unliked';
                } else {
                    $existing_like->restore();
                    //return 'reliked';
                }
            }
        }
    }

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

        Bouncer::allow('contributor')->to('create-pages');
        Bouncer::allow('contributor')->to('create-venues');
        Bouncer::allow('contributor')->to('create-events');

    }

}
