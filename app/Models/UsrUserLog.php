<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UsrUserLog
 * 
 * @property int $id
 * @property string $user_id
 * @property Carbon $login_date
 * @property Carbon $logout_date
 * @property string $login_ip
 * @property Carbon|null $date
 *
 * @package App\Models
 */
class UsrUserLog extends Model
{
	protected $table = 'usr_user_log';
	public $incrementing = true;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'login_date' => 'datetime',
		'logout_date' => 'datetime',
		'date' => 'datetime'
	];

	protected $fillable = [
		'id',
		'user_id',
		'login_date',
		'logout_date',
		'login_ip',
		'date'
	];
}
