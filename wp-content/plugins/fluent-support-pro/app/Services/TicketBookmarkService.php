<?php
namespace FluentSupportPro\App\Services;

use FluentSupport\App\Models\TagPivot;

class TicketBookmarkService
{

    /**
     * This `getExistingBookmarks` method is used to get the existing bookmarks for a ticket.
     * @param int $ticketId
     * @return mixed
     */
    public function getExistingBookmarks ( $ticketId )
    {
        return TagPivot::where('source_type', 'ticket_watcher')
            ->where('source_id', $ticketId)
            ->get(['tag_id']);
    }


    /**
     * This `removeOrAddBookmark` method is used to remove bookmarks from a ticket or store agent ids into an array to bookmark it later.
     * @param object $existingBookmarks
     * @param array $bookmarks
     * @param int $ticketId
     * @return array
     */
    public function removeOrAddToBookmarksList ( $existingBookmarks, $bookmarks, $ticketId  )
    {
        $bookmarksToAdd = [];

        foreach ($existingBookmarks as $bookmark) {
            if ( ! in_array( $bookmark->tag_id, $bookmarks ) ) {
                TagPivot::where('source_type', 'ticket_watcher')
                    ->where('source_id', $ticketId)
                    ->where('tag_id', $bookmark->tag_id)
                    ->delete();
            } else {
                $getExWatcherIds = array_column( $existingBookmarks->toArray(), 'tag_id' );
                $bookmarksToAdd = array_diff( $bookmarks, $getExWatcherIds );
            }
        }

        return $bookmarksToAdd;
    }

    /**
     * This `addBookmarks` method is used to add bookmarks to a ticket.
     * @param array $agentIds
     * @param int $ticketId
     * @return array
     */
    public function addBookmarks ( $agentIds, $ticketId )
    {
        foreach ($agentIds as $agentId) {
            TagPivot::firstOrCreate( [
                'source_type' => 'ticket_watcher',
                'source_id' => $ticketId,
                'tag_id' => absint( $agentId )
                ] );
        }
    }


    /**
     * This `removeBookmarks` method is used to remove bookmarks from a ticket.
     * @param array $agentIds
     * @param int $ticketId
     * @return void
     */
    public function removeBookmarks ( $agentIds, $ticketId )
    {
        $exsistingBookmarks = array_column( $this->getExistingBookmarks( $ticketId )->toArray(), 'tag_id' );
        $areBookmarked =  array_intersect( $agentIds,  $exsistingBookmarks );

        if ( ! empty( $areBookmarked ) ) {
            TagPivot::where('source_type', 'ticket_watcher')
                ->where('source_id', $ticketId)
                ->whereIn('tag_id', $areBookmarked)
                ->delete();
        }
    }
}