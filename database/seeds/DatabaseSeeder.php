<?php
use App\Models\User;
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

        $this->call(RolesSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(SubcategoriesTableSeeder::class);
        $this->call(SuperAdminDataSeeder::class);
        //         $this->call('UserDataSeeder');
        //         $this->call('VenueDataSeeder');
        //         $this->call('PageDataSeeder');
        //         $this->call('ShowDataSeeder');

        // switch (config('env')) {
        //     case 'local':
        //         $this->call(RolesSeeder::class);
        //         $this->call(CategoriesTableSeeder::class);
        //         $this->call(SubcategoriesTableSeeder::class);
        //         $this->call(SuperAdminDataSeeder::class);
        //         $this->call('UserDataSeeder');
        //         $this->call('VenueDataSeeder');
        //         $this->call('PageDataSeeder');
        //         $this->call('ShowDataSeeder');

        //         $this->call('EventDataSeeder');

        //         $this->call('MveDataSeeder');

        //         $this->call('UserLikesSeeder');

        //         break;

        //     case 'fresh':
        //         $this->call(RolesSeeder::class);
        //         $this->call(CategoriesTableSeeder::class);
        //         $this->call(SubcategoriesTableSeeder::class);
        //         $this->call(SuperAdminDataSeeder::class);
        //         break;

        //     case 'production':
        //         $this->call(CategoriesTableSeeder::class);
        //         $this->call(SubcategoriesTableSeeder::class);
        //         break;

        //     default:

        //         break;
        // }

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
        $venues = factory('App\Models\Venue', 300)->states('chicago')->create();
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
        DB::table('event_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $events = factory('App\Models\Event', 200)

        //States:
        //->states('tallimages')

            ->create()
            ->each(function ($ev) {

                // //Attach some categories
                $num_categories = Faker\Factory::create()->randomElement($array = array(0, 0, 1, 1, 2));
                //$num_categories = Faker\Factory::create()->randomElement($array = array(0, 0));
                while ($num_categories--) {
                    $ev->categories()->save(factory(App\Models\EventCategory::class)->make());
                }
                //dd($ev->categories->pluck('subcategory_id'));
                $categoriesjson = [];
                foreach ($ev->categories as $evcategory) {
                    $categoriesjson[] =
                        [
                        'category_id'      => $evcategory['category_id'],
                        'subcategory_id'   => $evcategory['subcategory_id'],
                        'subcategory_name' => $evcategory['subcategory_name'],
                    ];
                }
                if (!empty($categoriesjson)) {
                    $ev->categoriesjson = json_encode($categoriesjson);
                    $ev->save();
                }
                // //Attach a producer
                $num_producers = Faker\Factory::create()->optional($weight = 0.2)->randomElement($array = array(0, 0, 1, 1, 2));

                while ($num_producers--) {
                    $ev->eventproducer()->save(factory(App\Models\EventProducer::class)->make());
                }

                // //Make some participants
                $num_participants = Faker\Factory::create()->numberBetween($min = 1, $max = 5);
                while ($num_participants--) {
                    $ev->eventparticipants()->save(factory(App\Models\EventParticipant::class)->make());
                }
                $participantsjson = [];

                foreach ($ev->eventparticipants as $eventparticipant) {

                    $participantsjson[] =
                        [
                        'page_id'  => $eventparticipant['page_id'],
                        'name'     => $eventparticipant['name'],
                        //'info' => $eventshow_page['info'],
                        'imageurl' => $eventparticipant['imageurl'],
                        'start'    => $eventparticipant['start'],
                    ];
                }
                if (!empty($participantsjson)) {
                    $ev->participantsjson = json_encode($participantsjson);
                    $ev->save();
                }

                //Maybe attach a show:
                $num_shows = Faker\Factory::create()->randomElement($array = array(0, 0, 0, 0, 1, 1, 1, 1, 2));
                //$num_shows = Faker\Factory::create()->randomElement($array = array(0, 0));
                $showsjson = [];

                while ($num_shows--) {
                    $ev->eventshows()->save(factory(App\Models\EventShow::class)->make());
                }

                foreach ($ev->eventshows as $evshow) {
                    $eventshow_page = App\Models\Show::find($evshow['show_id'])->toArray();
                    $showsjson[]    =
                        [
                        'id'       => $eventshow_page['id'],
                        'name'     => $eventshow_page['name'],
                        'imageurl' => $eventshow_page['imageurl'],
                    ];
                }
                if (!empty($showsjson)) {
                    $ev->showsjson = json_encode($showsjson);
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
            'Comedy'        => ['Comedy', 'Stand-up Comedy', 'Comedic Writing', 'Improv Comedy', 'Sketch Comedy', 'Comedy Hosting'],
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
        DB::table('following')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $users  = App\Models\User::pluck('id')->toArray();
        $pages  = App\Models\Page::pluck('id')->toArray();
        $shows  = App\Models\Show::pluck('id')->toArray();
        $venues = App\Models\Venue::pluck('id')->toArray();
        $events = App\Models\Event::pluck('id')->toArray();

//make stuff for glypher
        $num_pagelikes  = Faker\Factory::create()->numberBetween($min = 0, $max = 10);
        $num_showlikes  = Faker\Factory::create()->numberBetween($min = 0, $max = 4);
        $num_venuelikes = Faker\Factory::create()->numberBetween($min = 0, $max = 3);

        $num_eventattends = Faker\Factory::create()->numberBetween($min = 20, $max = 200);
        $num_followings   = Faker\Factory::create()->numberBetween($min = 10, $max = 15);
//LIKE SOME PAGES
        $this->makelikes($num_pagelikes, 'App\Models\Page', $pages, 2);
//LIKE SOME SHOWS
        $this->makelikes($num_showlikes, 'App\Models\Show', $shows, 2);
//LIKE SOME VENUES
        $this->makelikes($num_venuelikes, 'App\Models\Venue', $venues, 2);

//Attend SOME Events
        $this->makeattends($num_eventattends, $events, 2, null);

//Friend some users
        $this->makefollowing($num_followings, $users, 2);
//make stuff for careleton
        //LIKE SOME PAGES
        $this->makelikes($num_pagelikes, 'App\Models\Page', $pages, 1);
//LIKE SOME SHOWS
        $this->makelikes($num_showlikes, 'App\Models\Show', $shows, 1);
//LIKE SOME VENUES
        $this->makelikes($num_venuelikes, 'App\Models\Venue', $venues, 1);

//Attend SOME Events
        $this->makeattends($num_eventattends, $events, 1, null);

//Friend some users
        $this->makefollowing($num_followings, $users, 1);

        foreach ($users as $user_id) {

            $num_pagelikes  = Faker\Factory::create()->numberBetween($min = 0, $max = 10);
            $num_showlikes  = Faker\Factory::create()->numberBetween($min = 0, $max = 4);
            $num_venuelikes = Faker\Factory::create()->numberBetween($min = 0, $max = 3);

            $num_eventattends = Faker\Factory::create()->numberBetween($min = 20, $max = 200);
            $num_followings   = Faker\Factory::create()->numberBetween($min = 10, $max = 15);

//LIKE SOME PAGES
            $this->makelikes($num_pagelikes, 'App\Models\Page', $pages, $user_id);
//LIKE SOME SHOWS
            $this->makelikes($num_showlikes, 'App\Models\Show', $shows, $user_id);
//LIKE SOME VENUES
            $this->makelikes($num_venuelikes, 'App\Models\Venue', $venues, $user_id);

//Attend SOME Events
            $this->makeattends($num_eventattends, $events, $user_id, null);

//Friend some users
            $this->makefollowing($num_followings, $users, $user_id);
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

    public function makefollowing($num_followings, $users, $user_id)
    {

        foreach (range(1, $num_followings) as $index) {

            $following_id = Faker\Factory::create()->randomElement($array = $users);
            $valid_status = [
                'reject'  => 0,
                'nothing' => 1,
                'pending' => 2,
                'accept'  => 3,
            ];
            $followstatus = Faker\Factory::create()->randomElement($array = array(0, 1, 1, 1, 2, 2, 3, 3, 3, 3));
            // $followbackstatus = Faker\Factory::create()->randomElement($array = array(0, 1, 1, 1, 2, 2, 2));

            $pyf = \App\Models\Following::firstOrCreate(
                ['user_id' => $user_id, 'following_id' => $following_id], ['status' => $followstatus]
            );

            // $inversefriendship = \App\Models\Following::firstOrCreate(
            //     ['user_id' => $following_id, 'following_id' => $user_id, 'status' => $followbackstatus]
            // );

        }
    }

}
