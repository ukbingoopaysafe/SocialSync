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
        if ($userRole === 'staff') {
            $userRole = 'designer';
        }

        $matrix = [
            'IDEA' => [
                'DRAFT' => ['admin', 'manager']
            ],
            'DRAFT' => [
                'PENDING_REVIEW' => ['admin', 'manager']
            ],
            'PENDING_REVIEW' => [
                'REVIEWED' => ['admin'],
                'DRAFT' => ['admin', 'manager']
            ],
            'REVIEWED' => [
                'APPROVED' => ['manager'],
                'CHANGES_REQUESTED' => ['manager'],
                'PENDING_REVIEW' => ['admin']
            ],
            'CHANGES_REQUESTED' => [
                'PENDING_REVIEW' => ['admin', 'manager']
            ],
            'APPROVED' => [
                'SCHEDULED' => ['admin', 'manager'],
                'REVIEWED' => ['manager'],
                'DRAFT' => ['manager']
            ],
            'SCHEDULED' => [
                'PUBLISHED' => ['system', 'admin', 'manager'],
                'APPROVED' => ['admin', 'manager'],
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
