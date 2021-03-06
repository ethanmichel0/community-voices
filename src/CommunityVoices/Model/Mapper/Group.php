<?php

namespace CommunityVoices\Model\Mapper;

use PDO;
use CommunityVoices\Model\Component\DataMapper;
use CommunityVoices\Model\Entity;

class Group extends DataMapper
{
    /**
     * @uses Group::fetchById
     */
    public function fetch(Entity\Group $group)
    {
        $this->fetchById($group);
    }

    /**
     * Maps a Group entity by the ID assigned on the instance. If no rows match
     * the entity's ID, the entity's ID is overwritten as null.
     *
     * @param  Group $group Group entity to fetch & map
     */
    private function fetchById(Entity\Group $group)
    {
        $query = "SELECT
                        group_entity.id                            AS id,
                        group_entity.label                         AS label,
                        CAST(group_entity.type AS UNSIGNED)        AS type,
                    FROM
                        `community-voices_groups` group_entity
                    WHERE
                        group_entity.id = :id";

        $statement = $this->conn->prepare($query);

        $statement->bindValue(':id', $group->getId());

        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $this->populateEntity($group, $result);
        }
    }

    public function save(Entity\Group $group)
    {
        if ($group->getId()) {
            $this->update($group);
            return ;
        }

        $this->create($group);
    }

    protected function update(Entity\Group $group)
    {
        $query = "UPDATE    `community-voices_groups`
                    SET     label = :label,
                            type = :type
                    WHERE   id = :id";

        $statement = $this->conn->prepare($query);

        $statement->bindValue(':id', $group->getId());
        $statement->bindValue(':label', $group->getLabel());
        $statement->bindValue(':type', $group->getType());
        $statement->execute();
    }

    protected function create(Entity\Group $group)
    {
        $query = "INSERT INTO   `community-voices_groups`
                                (label, type)
                    VALUES      (:label, :type)";

        $statement = $this->conn->prepare($query);

        $statement->bindValue(':label', $group->getLabel());
        $statement->bindValue(':type', $group->getType());

        $statement->execute();

        $group->setId($this->conn->lastInsertId());

        // This ensures compatibility with ContentCategory,
        // which uses a group ID as its primary key.
        if (method_exists($group, 'setGroupId')) {
            $group->setGroupId($group->getId());
        }
    }

    /**
     * Removes the database entry associated with the $group ID
     *
     * @param  Group $group Group entity to delete
     */
    public function delete(Entity\Group $group)
    {
        $query = "DELETE FROM   `community-voices_groups`
                    WHERE       id = :id";

        $statement = $this->conn->prepare($query);

        $statement->bindValue(':id', $group->getId());

        $statement->execute();
    }
}
