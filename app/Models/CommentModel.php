<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * Comment Model
 *
 * Handles comment data operations using stored procedures.
 * Comments are linked to entities via junction tables:
 *   Agent → tAgents_tComment → tComment
 *
 * Key Stored Procedures:
 * - spComment_Get: Get comment by CommentKey
 * - spComment_Save: Save/update comment (3 parameters: @Key, @Comment, @User)
 * - spComment_Delete: Delete comment
 * - spAgentComments_Get: Get CommentKeys for an AgentKey
 * - spAgentComments_Save: Link comment to agent
 *
 * @author Tony Lyle
 * @version 1.0 - CI4 Migration
 */
class CommentModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();

        // Initialize database connection if not already set
        if (!isset($this->db)) {
            $this->db = \Config\Database::connect();
        }

        // Set customer database context from session
        $session = \Config\Services::session();
        $customerDb = $session->get('customer_db');
        if ($customerDb) {
            $this->db->setDatabase($customerDb);
            log_message('info', "CommentModel initialized with database: {$customerDb}");
        }
    }

    /**
     * Get comment by CommentKey
     *
     * @param int $commentKey Comment key
     * @return array|null Comment data or null if not found
     */
    public function getComment(int $commentKey): ?array
    {
        if ($commentKey <= 0) {
            return null;
        }

        $results = $this->callStoredProcedure('spComment_Get', [$commentKey]);

        if (!empty($results) && is_array($results)) {
            $comment = $results[0];

            return [
                'CommentKey' => $commentKey,
                'Comment' => $comment['Comment'] ?? '',
                'CommentBy' => $comment['CommentBy'] ?? '',
                'CommentDate' => $comment['CommentDate'] ?? null,
                'EditedBy' => $comment['EditedBy'] ?? '',
                'EditedDate' => $comment['EditedDate'] ?? null
            ];
        }

        return null;
    }

    /**
     * Get all CommentKeys for a given AgentKey
     *
     * @param int $agentKey AgentKey from tAgents
     * @return array Array of CommentKey values
     */
    public function getCommentKeysForAgent(int $agentKey): array
    {
        if ($agentKey <= 0) {
            return [];
        }

        log_message('info', "CommentModel::getCommentKeysForAgent called for AgentKey: {$agentKey}");

        $results = $this->callStoredProcedure('spAgentComments_Get', [$agentKey]);

        log_message('info', "CommentModel::getCommentKeysForAgent - Results: " . json_encode($results));

        if (!empty($results) && is_array($results)) {
            // Extract CommentKey values from result set
            $commentKeys = array_column($results, 'CommentKey');
            log_message('info', "CommentModel::getCommentKeysForAgent - CommentKeys extracted: " . json_encode($commentKeys));
            return $commentKeys;
        }

        log_message('info', "CommentModel::getCommentKeysForAgent - No results found");
        return [];
    }

    /**
     * Save comment (create or update)
     *
     * @param array $commentData Comment data array
     * @param int $entityKey Entity key (AgentKey, DriverKey, etc.)
     * @param string $userId User ID for audit trail
     * @return int CommentKey of saved comment, or 0 on failure
     */
    public function saveComment(array $commentData, int $entityKey, string $userId): int
    {
        try {
            // Get CommentKey or generate new one
            $commentKey = $commentData['CommentKey'] ?? 0;
            $isNewComment = ($commentKey == 0);

            if ($isNewComment) {
                // New comment - get next key
                $commentKey = $this->getNextKey('tComment');
                if ($commentKey <= 0) {
                    log_message('error', 'Failed to get next comment key');
                    return 0;
                }
            }

            // Prepare parameters for spComment_Save (3 parameters)
            // @Key INT, @Comment VARCHAR(255), @User VARCHAR(25)
            $params = [
                $commentKey,                            // @Key INT
                $commentData['Comment'] ?? '',          // @Comment VARCHAR(255)
                $userId                                 // @User VARCHAR(25)
            ];

            // Call the stored procedure
            $this->callStoredProcedure('spComment_Save', $params);

            log_message('info', "spComment_Save executed successfully for CommentKey: {$commentKey}");

            // Link comment to entity via junction table (ONLY for new comments)
            if ($isNewComment && $entityKey > 0) {
                $this->linkCommentToAgent($commentKey, $entityKey);
            }

            return $commentKey;
        } catch (\Exception $e) {
            log_message('error', 'Error saving comment: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete comment
     *
     * @param int $commentKey Comment key to delete
     * @return bool True on success, false on failure
     */
    public function deleteComment(int $commentKey): bool
    {
        try {
            if ($commentKey <= 0) {
                return false;
            }

            $returnCode = $this->callStoredProcedureWithReturn('spComment_Delete', [$commentKey]);

            log_message('info', "spComment_Delete returned: {$returnCode} ({$this->getReturnCodeMessage($returnCode)}) for CommentKey: {$commentKey}");

            return ($returnCode === self::SRV_NORMAL);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting comment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Link comment to agent via junction table
     * Uses spAgentComments_Save to link
     *
     * @param int $commentKey Comment key
     * @param int $agentKey Agent key
     * @return bool True on success
     */
    private function linkCommentToAgent(int $commentKey, int $agentKey): bool
    {
        try {
            if ($commentKey > 0 && $agentKey > 0) {
                // Link comment to agent using spAgentComments_Save
                $this->callStoredProcedure('spAgentComments_Save', [$agentKey, $commentKey]);
                log_message('info', "Linked CommentKey {$commentKey} to AgentKey {$agentKey}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error linking comment to agent: ' . $e->getMessage());
            return false;
        }
    }
}
