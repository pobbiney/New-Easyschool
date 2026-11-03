<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class User extends  Authenticatable
{
	use HasFactory, Notifiable;
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'phone',
		'remember_token',
		'staff_id',
        'user_cat',
		'cat_id',
		'status',
		'photo',
	];

	public function category()
	{
		return $this->belongsTo(UserCat::class, 'user_cat', 'cat_id');
	}

	public function accessLinks()
	{
		return $this->hasMany(UserAccessLink::class, 'user_id');
	}

	public function getUserCategory (){

		$category = UserCat::find($this->user_cat);

		return $category ? $category->cat_name : 'Not Available';
	}

       public function categoryname()
{
	return $this->belongsTo(UserCat::class, 'user_cat', 'cat_id');
}

	public function getUserName ($id){

		if(User::where('id',$id)->get()->count() > 0){

			return User::find($id)->name;

		}else{

			return 'Not Available';
		}

		
	}

	public function staff()
{
    return $this->belongsTo(Staff::class, 'staff_id', 'id');
}

	public function profilePhotoPath(): ?string
	{
		$path = $this->photo ?: $this->staff?->picture;

		return filled($path) ? $path : null;
	}

	public function profilePhotoUrl(): ?string
	{
		$path = $this->profilePhotoPath();

		return $path ? asset($path) : null;
	}
}
