<?php
/**
 * WorkflowManager
 * Handles state transitions for posts.
 */

class WorkflowManager {
    /**
     * Checks if a user can transition a post to a new status.
     *
     * @param string $userRole The role of the user attempting the transition.
     * @param string $currentStatus The current status of the post.
     * @param string $newStatus The target status.
     * @return bool True if transition is allowed, false otherwise.
     */
    public static function canTransition($userRole, $currentStatus, $newStatus) {
        $matrix = [
            'IDEA' => [
                'DRAFT' => ['staff', 'admin', 'manager']
            ],
            'DRAFT' => [
                'PENDING_REVIEW' => ['staff', 'admin', 'manager']
            ],
            'PENDING_REVIEW' => [
                'REVIEWED' => ['admin', 'manager'],
                'CHANGES_REQUESTED' => ['admin', 'manager'],
                'DRAFT' => ['staff', 'admin', 'manager']
            ],
            'REVIEWED' => [
                'APPROVED' => ['manager'],
                'CHANGES_REQUESTED' => ['manager'],
                'PENDING_REVIEW' => ['admin', 'manager']
            ],
            'CHANGES_REQUESTED' => [
                'PENDING_REVIEW' => ['staff', 'admin', 'manager']
            ],
            'APPROVED' => [
                'SCHEDULED' => ['admin', 'manager'],
                'DRAFT' => ['manager']
            ],
            'SCHEDULED' => [
                'PUBLISHED' => ['system'],
                'DRAFT' => ['admin', 'manager']
            ]
        ];

        // Enforce strictly via mapping matrix
        if (isset($matrix[$currentStatus][$newStatus])) {
            return in_array($userRole, $matrix[$currentStatus][$newStatus], true);
        }

        return false;
    }
}
