<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

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
        $lnglatval   = $lng . ', ' . $lat;
        $glypherinfo = [
            'name'        => "Shawn 'glypher' Dalton",
            'username'    => "glypher",
            'email'       => "glypher@gmail.com",
            'facebook_id' => '10109892803653991',
            'avatar'      => 'https://graph.facebook.com/v2.8/10109892803653991/picture?type=normal',
            'lat'         => $lat,
            'lng'         => $lng,
            'location'    => DB::raw("POINT($lnglatval)"),
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
            'location'    => DB::raw("POINT($lnglatval)"),
            'confirmed'   => '1',
            'slug'        => 'cmaybell',
            'created_at'  => $datetime,
            'updated_at'  => $datetime,
        ];

        $id   = DB::table('users')->insertGetId($careletoninfo);
        $user = \app\models\User::find($id);
        \Auth::login($user);
        \Auth::user()->assign('superadmin');

        $id   = DB::table('users')->insertGetId($glypherinfo);
        $user = \app\models\User::find($id);

        //Logging in glypher so we can do all the seeding
        \Auth::login($user);
        \Auth::user()->assign('superadmin');

    }
}
