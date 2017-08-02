<?php

namespace CommunityVoices\Model\Mapper;

use PDO;
use CommunityVoices\Model\Component\DataMapper;
use CommunityVoices\Model\Entity as Entity;

class ContentCategory extends Group
{
    protected static $table = '`community-voices_content-categories`';

    public function fetch(Entity\Group $contentCategory)
    {
        $this->fetchById($contentCategory);
    }

    private function fetchById(Entity\Group $contentCategory)
    {
        $query = "SELECT    parent.id,
                            parent.label,
                            parent.type,
                            child.media_filename
                    FROM    " . parent::$table . " parent
                    JOIN    " . self::$table . " child
                    ON      parent.id = child.group_id
                    WHERE   parent.id = :id";

        $statement = $this->conn->prepare($query);

        $statement->bindValue(':id', $contentCategory->getId());

        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $this->applyValues($contentCategory, $result);
        }
    }

    public function save(Entity\Group $contentCategory)
    {
        if ($contentCategory->getId()) {
            $this->update($contentCategory);
            return ;
        }

        $this->create($contentCategory);
    }

    protected function update(Entity\Group $contentCategory)
    {
        parent::update($contentCategory);

        $query = "UPDATE    " . self::$table . "
                    SET     media_filename = :media_filename
                    WHERE   group_id = :group_id";

        $statement = $this->conn->prepare($query);

        $statement->bindValue(':group_id', $contentCategory->getId());

        $statement->execute();
    }

    protected function create(Entity\Group $contentCategory)
    {
        parent::create($contentCategory);

        $query = "INSERT INTO   " . self::$table . "
                                (group_id, media_filename)
                    VALUES      (:group_id, :media_filename)";

        $statement = $this->conn->prepare($query);

        $statement->bindValue(':group_id', $contentCategory->getId());
        $statement->bindValue(':media_filename', $contentCategory->getMediaFilename());

        $statement->execute();
    }

    public function delete(Entity\Group $contentCategory)
    {
        parent::delete($contentCategory); //deletion cascades
    }
}