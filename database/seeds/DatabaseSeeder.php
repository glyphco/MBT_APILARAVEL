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

        $this->call('RolesSeeder');
        $this->call('CategoriesSeeder');
        $this->call('SuperAdminDataSeeder');

        if (App::environment('local')) {
            // run production queries or model factories

            //Fake Data Follows:
            $this->call('UserDataSeeder');
            $this->call('VenueDataSeeder');
            $this->call('PageDataSeeder');
            $this->call('ShowDataSeeder');

            $this->call('EventDataSeeder');

            $this->call('MveDataSeeder');

            $this->call('UserLikesSeeder');

        } else {
            // run local queries or model factories

            //Fake Data Follows:
            $this->call('UserDataSeeder');
            $this->call('VenueDataSeeder');
            $this->call('PageDataSeeder');
            $this->call('ShowDataSeeder');

            $this->call('EventDataSeeder');

            $this->call('MveDataSeeder');

            $this->call('UserLikesSeeder');
        }

    }

}

class SuperAdminDataSeeder extends Seeder
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
        $careletoninfo = [
            'name'        => "Carleton Maybell",
            'username'    => "cmaybell",
            'email'       => "1555815747766321@facebook.com",
            'facebook_id' => '1555815747766321',
            'avatar'      => 'https://graph.facebook.com/v2.8/1555815747766321/picture?type=normal',
            'lat'         => $lat,
            'lng'         => $lng,
            'location'    => DB::raw("POINT($latlngval)"),
            'confirmed'   => '1',
            'slug'        => 'cmaybell',
            'created_at'  => $datetime,
            'updated_at'  => $datetime,
        ];

        $id   = DB::table('users')->insertGetId($careletoninfo);
        $user = User::find($id);
        \Auth::login($user);
        \Auth::user()->assign('superadmin');

        $id   = DB::table('users')->insertGetId($glypherinfo);
        $user = User::find($id);

        //Logging in glypher so we can do all the seeding
        \Auth::login($user);
        \Auth::user()->assign('superadmin');

    }
}

class UserDataSeeder extends Seeder
{
    public function run()
    {

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
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

//my seeding for the percentages (lots of poerfomers, some producers, few shows, few perfomer/producers)

        //make at least one of each page:
        $pages = factory('App\Models\Page', 10)
            ->states('chicago', 'production')
            ->create();
        $pages = factory('App\Models\Page', 50)
            ->states('chicago', 'participant')
            ->create();
        $pages = factory('App\Models\Page', 7)
            ->states('chicago', 'productionparticipant')
            ->create();

    }
}

class ShowDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('shows')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $shows = factory('App\Models\Show', 15)
            ->create();
    }
}

class EventDataSeeder extends Seeder
{
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('events')->truncate();
        DB::table('event_participants')->truncate();
        DB::table('event_producers')->truncate();
        DB::table('event_shows')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $events = factory('App\Models\Event', 200)
            ->create()
            ->each(function ($ev) {

                // //Make some categories
                $num_categories = Faker\Factory::create()->randomElement($array = array(1, 1, 1, 1, 2));
                foreach (range(1, $num_categories) as $index) {
                    $ev->categories()->save(factory(App\Models\EventCategory::class)->make());
                }

                // //Attach a producer
                $num_producers = Faker\Factory::create()->optional($weight = 0.2)->randomElement($array = array(1, 1, 1, 1, 2));
                foreach (range(1, $num_producers) as $index) {
                    $ev->eventproducer()->save(factory(App\Models\EventProducer::class)->make());
                }

                // //Make some participants
                $num_participants = Faker\Factory::create()->numberBetween($min = 1, $max = 5);
                foreach (range(1, $num_participants) as $index) {
                    $ev->eventparticipants()->save(factory(App\Models\EventParticipant::class)->make());
                }

                //Maybe attach a show:
                $num_shows = Faker\Factory::create()->optional($weight = 0.5)->randomElement($array = array(1, 1, 1, 1, 1, 1, 1, 1, 2)); // 50% chance of NULL
                //echo ($num_shows);
                $showjson = null;
                if ($num_shows) {
                    foreach (range(1, $num_shows) as $index) {
                        $ev->eventshows()->save(factory(App\Models\EventShow::class)->make());
                    }
                    $showjson = [];
                    foreach ($ev->eventshows as $evshow) {
                        $eventshow_page = App\Models\Show::find($evshow['show_id'])->toArray();
                        $showjson[]     =
                            [
                            'id'       => $eventshow_page['id'],
                            'name'     => $eventshow_page['name'],
                            'imageurl' => $eventshow_page['imageurl'],
                        ];
                    }
                    $ev->showjson = json_encode($showjson);
                    $ev->save();
                }

            });
    }
}

class MveDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('mves')->truncate();
        DB::table('mve_producers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $shows = factory('App\Models\Mve', 5)
            ->create()
            ->each(function ($mve) {

                $num_producers = Faker\Factory::create()->optional($weight = 0.5)->randomElement($array = array(1, 1, 1, 2)); // 20% chance of NULL
                if ($num_producers) {
                    foreach (range(1, $num_producers) as $index) {
                        $mve->mveproducers()->save(factory(App\Models\MveProducer::class)->make());
                    }
                }

//Add some eventvenues to this MVE
                $num_events = Faker\Factory::create()->randomElement($array = array(3, 5, 7));
                if ($num_events) {
                    App\Models\Event::wherenull('mve_id')->inRandomOrder()->take($num_events)->get()
                        ->each(function ($ev) use ($mve) {
                            //dump($ev);
                            $ev->mve_id = $mve->id;
                            //dd($ev);
                            $ev->save();
                        });

                }

            });
    }

}

class CategoriesSeeder extends Seeder
{
    //php artisan db:seed --class=CategoriesSeeder
    public function run()
    {

        $categories = [
            'Comedy'        => ['Comedy', 'Stand-up Comedy', 'Comedic Writing', 'Improv Comedy', 'Sketch Comedy'],
            'Music'         => ['Music', 'Rock', 'Pop', 'Funk', 'Psychedelic', 'Emo', 'Pop-Punk', 'Acoustic', 'Country', 'Americana', 'Covers', 'Rock Band', 'Drums', 'Guitars', 'Vocals', 'Bass', 'Piano', 'Trumpet'],
            'Fun and Games' => ['Fun and Games', 'Pinball', 'Arcade', 'Video Games', 'Board Games'],
            'Arts'          => ['Arts', 'Theatre', 'Film and Movies', 'Visual Arts'],
            'Sports'        => ['Sports', 'Biking', 'Roller Derby', 'Baseball', 'Basketball', 'Softball'],
            'Science'       => ['Science', 'Astronomy', 'Biology', 'History'],

        ];
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('categories')->truncate();
        DB::table('subcategories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        foreach ($categories as $category => $subCategories) {
            $id = \App\Models\Category::create(['name' => $category])->id;
            foreach ($subCategories as $subCategory) {
                \App\Models\Subcategory::create([
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
        DB::table('attending')->truncate();
        DB::table('friendships')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $users  = App\Models\User::pluck('id')->toArray();
        $pages  = App\Models\Page::pluck('id')->toArray();
        $shows  = App\Models\Show::pluck('id')->toArray();
        $venues = App\Models\Venue::pluck('id')->toArray();
        $events = App\Models\Event::pluck('id')->toArray();

        foreach ($users as $user_id) {

            $num_pagelikes  = Faker\Factory::create()->numberBetween($min = 0, $max = 10);
            $num_showlikes  = Faker\Factory::create()->numberBetween($min = 0, $max = 4);
            $num_venuelikes = Faker\Factory::create()->numberBetween($min = 0, $max = 3);

            $num_eventattends = Faker\Factory::create()->numberBetween($min = 0, $max = 10);
            $num_friends      = Faker\Factory::create()->numberBetween($min = 0, $max = 5);

//LIKE SOME PAGES
            $this->makelikes($num_pagelikes, 'App\Models\Page', $pages, $user_id);
//LIKE SOME SHOWS
            $this->makelikes($num_showlikes, 'App\Models\Show', $shows, $user_id);
//LIKE SOME VENUES
            $this->makelikes($num_venuelikes, 'App\Models\Venue', $venues, $user_id);

//Attend SOME Events
            $this->makeattends($num_eventattends, $events, $user_id, null);

//Friend some users
            $this->makefriendships($num_friends, $users, $user_id);
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

    public function makeattends($num_attends, $event_array, $user_id = null, $rank = null)
    {
        $valid_ranks = [
            0 => 'not attending',
            1 => 'maybe attending',
            2 => 'wish i could',
            3 => 'attending',
        ];

        if (!$rank || !array_key_exists($forcerank, $this->valid_ranks)) {
            $rank = Faker\Factory::create()->numberBetween($min = 0, $max = 3);
        }

        foreach (range(1, $num_attends) as $index) {

            $event_id = Faker\Factory::create()->randomElement($array = $event_array);

            $attending = \App\Models\Attending::updateOrCreate(
                ['event_id' => $event_id, 'user_id' => $user_id],
                ['rank' => $rank]
            );

        }
    }

    public function makefriendships($num_friends, $users, $user_id)
    {

        foreach (range(1, $num_friends) as $index) {

            $friend_id = Faker\Factory::create()->randomElement($array = $users);

            $friendship = \App\Models\Friendships::firstOrCreate(
                ['user_id' => $user_id, 'friend_id' => $friend_id]
            );
            $inversefriendship = \App\Models\Friendships::firstOrCreate(
                ['user_id' => $friend_id, 'friend_id' => $user_id]
            );

        }
    }

}

class RolesSeeder extends Seeder
{
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
