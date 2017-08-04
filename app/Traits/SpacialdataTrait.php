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

    // public function newQuery($excludeDeleted = true)
    // {
    //     $raw = '';
    //     foreach ($this->geofields as $column) {
    //         $raw .= ' ST_AsText(' . $column . ') as ' . $column . ' ';
    //     }

    //     return parent::newQuery($excludeDeleted)->addSelect('*', DB::raw($raw));
    // }

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
            return $query
                ->selectRaw('( ' . $MetersToMiles . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance')

                // ->selectRaw('( ' . $vert . ' * acos( cos( radians(' . $lat . ') ) *
                //                cos( radians( lat ) )
                //                * cos( radians( lng ) - radians(' . $lng . ')
                //                ) + sin( radians(' . $lat . ') ) *
                //                sin( radians( lat ) ) )
                //              ) AS distance')

                ->havingRaw('( ' . $MetersToMiles . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) < ' . $max . '');
            //->orderBy('distance');

        }

        return $query
            ->selectRaw('( ' . $MetersToMiles . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance')

            // ->selectRaw('( ' . $vert . ' * acos( cos( radians(' . $lat . ') ) *
            //                    cos( radians( lat ) )
            //                    * cos( radians( lng ) - radians(' . $lng . ')
            //                    ) + sin( radians(' . $lat . ') ) *
            //                    sin( radians( lat ) ) )
            //                  ) AS distance')

            ->orderBy('distance');

    }

    public function scopeDistance($query, $dist, $location)
    {
        return $query->whereRaw('st_distance(location,POINT(' . $location . ')) < ' . $dist);
    }

    public function scopeNear($subQuery, $lat, $lng, $max)
    {

        $km             = '6371';
        $DegreesToMiles = '3959';
        $MetersToMiles  = "0.000621371";
        $vert           = $DegreesToMiles;
        $metersToKm     = .0001;

        $spherelocation = $lng . ',' . $lat;

        //Generating Query
        $item_distance_query = '* , (3959 * ' .
        'acos( cos( radians(?) ) ' . //lat
        '* cos( radians( lat ) ) ' .
        '* cos( radians( lng ) - radians(?) ) ' . //long
        '+ sin( radians(?) ) ' . //lat
        '* sin( radians( lat ) ) ' .
            ') ) as distance'; //distance3

        $subQuery->getQuery()->selectRaw($item_distance_query,
            [$lat, $lng, $max]
        );

        // $item_distance_query = '* , (? * ST_Distance_Sphere(location,POINT(?))) as distance';
        // $subQuery->getQuery()->selectRaw($item_distance_query,
        //     [$DegreesToMiles, $spherelocation]
        // );
        $rawQuery = self::getSql($subQuery);
        return DB::table(DB::raw("(" . $rawQuery . ") as item"))
            ->where('distance', '<', $max);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    private static function getSql($builder)
    {
        $sql = $builder->toSql();
        foreach ($builder->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql   = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }

    public function scopeProximity($query, $lat, $lng, $radius, $units)
    {

        $radius = $radius ? $radius : 500;
        $table  = $this->getTable();
        if ($units == 'KM') {
            $distanceUnit = 111.045;
        } else {
            $distanceUnit = 69.0;
        }

        $haversine = $table . '.*' . sprintf(', (%f * DEGREES(ACOS(COS(RADIANS(%f)) * COS(RADIANS(lat)) * COS(RADIANS(%f - lng)) + SIN(RADIANS(%f)) * SIN(RADIANS(lat))))) AS distance', $distanceUnit, $lat, $lng, $lat);

        $subselect = clone $query;
        $subselect->selectRaw(DB::raw($haversine)); // Optimize haversine query: http://www.plumislandmedia.net/mysql/haversine-mysql-nearest-loc/

        $latDistance      = $radius / $distanceUnit;
        $latNorthBoundary = $lat - $latDistance;
        $latSouthBoundary = $lat + $latDistance;
        $subselect->whereRaw(sprintf("lat BETWEEN %f AND %f", $latNorthBoundary, $latSouthBoundary));

        $lngDistance     = $radius / ($distanceUnit * cos(deg2rad($lat)));
        $lngEastBoundary = $lng - $lngDistance;
        $lngWestBoundary = $lng + $lngDistance;
        $subselect->whereRaw(sprintf("lng BETWEEN %f AND %f", $lngEastBoundary, $lngWestBoundary));

        $query
            ->from(DB::raw('(' . $subselect->toSql() . ') as ' . $table))
            ->where('distance', '<=', $radius);
    }

}
