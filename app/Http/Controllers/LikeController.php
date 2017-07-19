<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Like;

class LikeController extends BaseController
{
    public function likePage($id)
    {
        if (\App\Models\Page::find($id)) {
            return $this->showResponse($this->handleLike('App\Models\Page', $id));
        }
        return $this->notFoundResponse();
    }

    public function likeVenue($id)
    {
        if (\App\Models\Venue::find($id)) {
            return $this->showResponse($this->handleLike('App\Models\Venue', $id));
        }
        return $this->notFoundResponse();
    }

    public function likeShow($id)
    {
        if (\App\Models\Show::find($id)) {
            return $this->showResponse($this->handleLike('App\Models\Show', $id));
        }
        return $this->notFoundResponse();
    }

    public function handleLike($type, $id)
    {
        $existing_like = Like::withTrashed()->whereLikeableType($type)->whereLikeableId($id)->whereUserId(\Auth::id())->first();

        if (is_null($existing_like)) {
            Like::create([
                'user_id'       => \Auth::id(),
                'likeable_id'   => $id,
                'likeable_type' => $type,
            ]);
            return 'liked';
        } else {
            if (is_null($existing_like->deleted_at)) {
                $existing_like->delete();
                return 'unliked';
            } else {
                $existing_like->restore();
                return 'reliked';
            }
        }
    }

}
