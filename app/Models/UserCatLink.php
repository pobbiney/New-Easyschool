<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserCatLink
 * 
 * @property int $cat_id
 * @property int $link_id
 *
 * @package App\Models
 */
class UserCatLink extends Model
{
	protected $table = 'user_cat_links';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'cat_id' => 'int',
		'link_id' => 'int'
	];

	protected $fillable = [
		'cat_id',
		'link_id'
	];
}
