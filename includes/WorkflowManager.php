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
                'DRAFT' => ['staff', 'manager', 'admin']
            ],
            'DRAFT' => [
                'PENDING_REVIEW' => ['staff', 'manager', 'admin']
            ],
            'PENDING_REVIEW' => [
                'REVIEWED' => ['admin', 'manager'],
                'CHANGES_REQUESTED' => ['admin', 'manager'],
                'DRAFT' => ['staff']
            ],
            'REVIEWED' => [
                'APPROVED' => ['manager', 'admin'],
                'CHANGES_REQUESTED' => ['manager', 'admin']
            ],
            'CHANGES_REQUESTED' => [
                'PENDING_REVIEW' => ['staff', 'manager', 'admin']
            ],
            'APPROVED' => [
                'SCHEDULED' => ['manager', 'admin'],
                'DRAFT' => ['manager', 'admin']
            ],
            'SCHEDULED' => [
                'PUBLISHED' => ['system'],
                'DRAFT' => ['manager', 'admin']
            ]
        ];

        // If admin, option to allow all logically valid transitions defined in matrix
        if ($userRole === 'admin') {
            if (isset($matrix[$currentStatus][$newStatus])) {
                return true;
            }
        }

        if (isset($matrix[$currentStatus][$newStatus])) {
            return in_array($userRole, $matrix[$currentStatus][$newStatus], true);
        }

        return false;
    }
}
