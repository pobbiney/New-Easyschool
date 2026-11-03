<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UsrUser
 * 
 * @property int $id
 * @property string $username
 * @property string $password
 * @property Carbon $user_date
 * @property int $user_cat
 * @property string $user_fullname
 * @property string $staff_id
 * @property string|null $branch
 * @property int $status
 * @property Carbon|null $last_seen
 * @property string $created_by
 * @property string|null $phone_number
 * @property string|null $region_id
 * @property string|null $district_id
 * @property string|null $updated_on
 * @property string|null $updated_by
 * @property string|null $login_status
 * @property string|null $password_change_date
 *
 * @package App\Models
 */
class UsrUser extends Model
{
	protected $table = 'usr_users';
	public $timestamps = false;

	protected $casts = [
		'user_date' => 'datetime',
		'user_cat' => 'int',
		'status' => 'int',
		'last_seen' => 'datetime'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'username',
		'password',
		'user_date',
		'user_cat',
		'user_fullname',
		'staff_id',
		'branch',
		'status',
		'last_seen',
		'created_by',
		'phone_number',
		'region_id',
		'district_id',
		'updated_on',
		'updated_by',
		'login_status',
		'password_change_date'
	];
}
