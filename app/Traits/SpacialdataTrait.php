<?php namespace App\Traits;

use DB;

// https://www.codetutorial.io/geo-spatial-mysql-laravel-5/

trait SpacialDataTrait
{

    protected $geofields = ['location'];

    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = DB::raw("POINT($value)");
    }

    public function getLocationAttribute($value)
    {

        $loc = substr($value, 6);
        $loc = preg_replace('/[ ,]+/', ',', $loc, 1);

        //return 'nope';
        return substr($loc, 0, -1);
    }

    public function newQuery($excludeDeleted = true)
    {
        $raw = '';
        foreach ($this->geofields as $column) {
            $raw .= ' ST_AsText(' . $column . ') as ' . $column . ' ';
        }

        return parent::newQuery($excludeDeleted)->addSelect('*', DB::raw($raw));
    }

    public function scopeWithDistanceFrom($query, $max, $location)
    {

        if ($max) {
            $maxclean = "'$max'";
            return $query->selectRaw('st_distance(location,POINT(' . $location . ')) as distance')
                ->havingRaw('distance < ' . $max . '')
                ->orderBy('distance');

            // return $query->selectRaw('( 6371 * acos( cos( radians(?) ) *
            //                    cos( radians( latitude ) )
            //                    * cos( radians( longitude ) - radians(?)
            //                    ) + sin( radians(?) ) *
            //                    sin( radians( latitude ) ) )
            //                  ) AS distance', [$latitude, $longitude, $latitude])
            //     ->havingRaw("distance < ?", [$limit]);
        }

        return $query->selectRaw('st_distance(location,POINT(' . $location . ')) as distance')
            ->orderBy('distance');

        // return $query->selectRaw('( 6371 * acos( cos( radians(?) ) *
        //                        cos( radians( latitude ) )
        //                        * cos( radians( longitude ) - radians(?)
        //                        ) + sin( radians(?) ) *
        //                        sin( radians( latitude ) ) )
        //                      ) AS distance', [$latitude, $longitude, $latitude]);

    }

    public function scopeWithRadiusFrom($query, $max, $latlng)
    {

        list($lat, $lng) = explode(",", $latlng);
        $km              = '6371';
        $DegreesToMiles  = '3959';
        $MetersToMiles   = "0.000621371";
        $vert            = $DegreesToMiles;
        $metersToKm      = .0001;

        $spherelocation = $lng . ',' . $lat;

        if ($max) {
            $maxclean = "'$max'";
            return $query
                ->selectRaw('( ' . $MetersToMiles . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance')

                // ->selectRaw('( ' . $vert . ' * acos( cos( radians(' . $lat . ') ) *
                //                cos( radians( lat ) )
                //                * cos( radians( lng ) - radians(' . $lng . ')
                //                ) + sin( radians(' . $lat . ') ) *
                //                sin( radians( lat ) ) )
                //              ) AS distance')

                ->havingRaw('distance < ' . $max . '')
                ->orderBy('distance');

        }

        return $query
            ->selectRaw('( ' . $MetersToMiles . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance')

            ->selectRaw('( ' . $vert . ' * acos( cos( radians(' . $lat . ') ) *
                               cos( radians( lat ) )
                               * cos( radians( lng ) - radians(' . $lng . ')
                               ) + sin( radians(' . $lat . ') ) *
                               sin( radians( lat ) ) )
                             ) AS distance')

            ->orderBy('distance');

    }

    public function scopeDistance($query, $dist, $location)
    {
        return $query->whereRaw('st_distance(location,POINT(' . $location . ')) < ' . $dist);
    }

}
