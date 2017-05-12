<?php
namespace App\Traits;
use DB;

// https://www.codetutorial.io/geo-spatial-mysql-laravel-5/

trait SpacialDataTrait {

	protected $geofields = ['location'];

	public function setLocationAttribute($value) {
		$this->attributes['location'] = DB::raw("POINT($value)");
	}

	public function getLocationAttribute($value) {

		$loc = substr($value, 6);
		$loc = preg_replace('/[ ,]+/', ',', $loc, 1);

		//return 'nope';
		return substr($loc, 0, -1);
	}

	public function newQuery($excludeDeleted = true) {
		$raw = '';
		foreach ($this->geofields as $column) {
			$raw .= ' astext(' . $column . ') as ' . $column . ' ';
		}

		return parent::newQuery($excludeDeleted)->addSelect('*', DB::raw($raw));
	}

	public function scopeDistance($query, $dist, $location) {
		return $query->whereRaw('st_distance(location,POINT(' . $location . ')) < ' . $dist);
	}

}
