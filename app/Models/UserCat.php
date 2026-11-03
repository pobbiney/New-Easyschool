<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserCat
 * 
 * @property int $cat_id
 * @property string $cat_name
 * @property string $status
 *
 * @package App\Models
 */
class UserCat extends Model
{

	protected $table = 'user_cat';
	protected $primaryKey = 'cat_id';
	public $timestamps = false;

	protected $fillable = [
		'cat_name',
		'status'
	];
}
