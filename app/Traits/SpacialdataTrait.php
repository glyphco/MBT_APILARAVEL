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

    public function scopeNear($query, $lat, $lng, $dist = 0, $units = Miles)
    {

        $dist  = $dist ? $dist : 5000;
        $table = $this->getTable();
        if ($units == 'KM') {
            $distanceUnit = 111.045;
            $vert         = 0.001;
        } else if ($units == 'METERS') {
            $distanceUnit = 111045;
            $vert         = 1;
        } else {
            $distanceUnit = 69.0;
            $vert         = 0.000621371;
        }

        //$vert = 0.000621371;

        $spherelocation = $lng . ',' . $lat;

        $query->
            selectRaw($table . '.*,( ' . $vert . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance');

        //$query->selectRaw(' ST_AsText(location) as locationx');

        if ($dist) {
            $query->where(DB::raw('( ' . $vert . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . ')))'), '<', $dist);
        }

        return $query;
    }

    public function scopeDistance($query, $lat, $lng, $units = 'METERS')
    {

        $table = $this->getTable();
        if ($units == 'KM') {
            $vert = 0.001;
        } else if ($units == 'METERS') {
            $vert = 1;
        } else {
            $vert = 0.000621371;
        }

        $spherelocation = $lng . ',' . $lat;

        return $query->
            selectRaw($table . '.*,( ' . $vert . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance');

    }

    public function scopeWithRadiusFromWithSub($query, $lat, $lng, $dist, $units)
    {

        $dist  = $dist ? $dist : 500;
        $table = $this->getTable();
        if ($units == 'KM') {
            $distanceUnit = 111.045;
            $vert         = 0.001;
        } else {
            $distanceUnit = 69.0;
            $vert         = 0.000621371;
        }

        //$vert = 0.000621371;

        $spherelocation = $lng . ',' . $lat;

        $sphere = $table . '.*' . sprintf(', (%f * ST_Distance_Sphere(location,POINT(%f,%f))) as distance', $vert, $lng, $lat);

        $subselect = clone $query;
        $subselect->selectRaw(DB::raw($sphere)); // Optimize haversine query: http://www.plumislandmedia.net/mysql/haversine-mysql-nearest-loc/

        $latDistance      = $dist / $distanceUnit;
        $latNorthBoundary = $lat - $latDistance;
        $latSouthBoundary = $lat + $latDistance;
        $subselect->whereRaw(sprintf("lat BETWEEN %f AND %f", $latNorthBoundary, $latSouthBoundary));

        $lngDistance     = $dist / ($distanceUnit * cos(deg2rad($lat)));
        $lngEastBoundary = $lng - $lngDistance;
        $lngWestBoundary = $lng + $lngDistance;
        $subselect->whereRaw(sprintf("lng BETWEEN %f AND %f", $lngEastBoundary, $lngWestBoundary));

        $query
            ->from(DB::raw('(' . $subselect->toSql() . ') as goobahey' . $table))
            ->where('distance', '<=', $dist);

        return $query;

        $query->
            selectRaw($table . '.*,( ' . $vert . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . '))) as distance');

        $latDistance      = $dist / $distanceUnit;
        $latNorthBoundary = $lat - $latDistance;
        $latSouthBoundary = $lat + $latDistance;

        //$subselect->whereRaw(sprintf("lat BETWEEN %f AND %f", $latNorthBoundary, $latSouthBoundary));

        if ($dist) {
            $query->where(DB::raw('( ' . $vert . ' * ST_Distance_Sphere(location,POINT(' . $spherelocation . ')))'), '<', $dist);
        }

        return $query;
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
