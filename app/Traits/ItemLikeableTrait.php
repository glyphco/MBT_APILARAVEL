<?php
namespace App\Traits;

trait ItemLikeableTrait
{

    public function getlikes($id)
    {

        if ($data = \App\Models\Like::where('likeable_type', self::MODEL)->where('likeable_id', $id)->get()) {
            return $this->showResponse($data);
        }
        return $this->notFoundResponse();

    }

    public function like($id)
    {
        //Only Like/unlike Public and Confirmed Items
        $m = self::MODEL;
        if ($m::find($id)) {
            return $this->showResponse($this->handleLike(self::MODEL, $id));
        }
        return $this->notFoundResponse();
    }

    public function handleLike($type, $id)
    {
        $existing_like = \App\Models\Like::withTrashed()->whereLikeableType($type)->whereLikeableId($id)->whereUserId(\Auth::id())->first();

        if (is_null($existing_like)) {
            \App\Models\Like::create([
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
