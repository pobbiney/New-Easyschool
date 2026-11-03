<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLink
 * 
 * @property int $link_id
 * @property string|null $link_url
 * @property string|null $link_name
 * @property string|null $link_target
 * @property string|null $link_image
 * @property int|null $link_parent
 * @property string $page_id
 * @property string|null $page_id_sub
 * @property string $status
 *
 * @package App\Models
 */
class UserLink extends Model
{
	protected $table = 'user_links';
	protected $primaryKey = 'link_id';
	public $timestamps = false;

	protected $casts = [
		'link_parent' => 'int'
	];

	protected $fillable = [
		'link_url',
		'link_name',
		'link_target',
		'link_image',
		'link_parent',
		'page_id',
		'page_id_sub',
		'status'
	];
}
