<?php

namespace App\Models;

use App\Models\BaseModel;

class TeamModel extends BaseModel
{
    protected $table = 'tTeam';
    protected $primaryKey = 'TeamKey';

    /**
     * Get all teams for a division
     *
     * @param int $companyID The company ID
     * @param int $divisionID The division ID
     * @return array Array of teams
     */
    public function getTeamsByDivision(int $companyID, int $divisionID): array
    {
        $results = $this->callStoredProcedure('spTeams_GetByDivision', [$companyID, $divisionID]);
        return $results ?? [];
    }

    /**
     * Get a single team by TeamKey
     *
     * @param int $teamKey The team key
     * @return array|null Team data or null if not found
     */
    public function getTeam(int $teamKey): ?array
    {
        $results = $this->callStoredProcedure('spTeam_Get', [$teamKey]);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Save (create or update) a team
     *
     * @param array $teamData Team data array
     * @return int Return code (0 = success, 97 = failed, 98 = invalid parent)
     */
    public function saveTeam(array $teamData): int
    {
        $params = [
            $teamData['TeamKey'] ?? 0,
            $teamData['TeamName'] ?? null,
            $teamData['TeamPhone'] ?? null,
            $teamData['TeamFax'] ?? null,
            $teamData['ReportGroup'] ?? null,
            $teamData['AgentKey'] ?? null,
            $teamData['AgentPay'] ?? null,
            $teamData['CompanyID'] ?? null,
            $teamData['DivisionID'] ?? null
        ];

        return $this->callStoredProcedureWithReturn('spTeam_Save', $params);
    }

    /**
     * Delete a team
     * NOTE: tTeam has triggers that will set TeamKey to NULL in tUser and tUnit
     *
     * @param int $teamKey The team key
     * @return int Return code (0 = success, 99 = not found)
     */
    public function deleteTeam(int $teamKey): int
    {
        return $this->callStoredProcedureWithReturn('spTeam_Delete', [$teamKey]);
    }

    /**
     * Get next available TeamKey from tSurrogateKey
     *
     * @return int Next team key
     */
    public function getNextTeamKey(): int
    {
        return $this->getNextKey('Team');
    }
}
