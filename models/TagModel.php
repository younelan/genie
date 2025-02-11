<?php
class TagModel extends AppModel
{
    private $db;
    private $notes_table = 'tags';

    public function __construct($config)
    {
        $this->db = $config['connection'];
        $this->notes_table = $config['tables']['notes'] ?? 'tags';
    }

    public function getTags($memberId, $tagType = 'INDI')
    {
        $query = "SELECT * FROM $this->notes_table t
        WHERE row_id = :member_id AND tag_type = :tag_type";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->bindParam(':tag_type', $tagType);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listTags($memberId, $tagType = 'INDI')
    {
        $raw_tags = $this->getTags($memberId, $tagType);
        $tagList = [];
        foreach ($raw_tags as $tag) {
            if(!in_array($tag['tag'], $tagList)) {
                $tagList[] = $tag['tag'];
            }
        }
        return $tagList;
    }

    public function getTagString($memberId, $tagType = 'INDI') {
        return implode(",", $this->listTags($memberId, $tagType));
    }

    // Keep the row_id in database queries, but accept member_id in parameters
    public function addTag($newTag) {
        error_log("TagModel::addTag received: " . print_r($newTag, true));

        $tree_id = !empty($newTag['tree_id']) ? intval($newTag['tree_id']) : false;
        $row_id = !empty($newTag['row_id']) ? intval($newTag['row_id']) : false;    // Changed from member_id
        $tag = !empty($newTag['tag']) ? $newTag['tag'] : false;
        $tag_type = !empty($newTag['tag_type']) ? $newTag['tag_type'] : false;

        if (!$tree_id || !$row_id || !$tag || !$tag_type) {
            error_log("TagModel::addTag validation failed");
            return false;
        }

        $query = "INSERT INTO $this->notes_table (tag, tree_id, row_id, tag_type, created_at, updated_at) 
                  VALUES (:tag_name, :tree_id, :row_id, :tag_type, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_name', $tag);
        $stmt->bindParam(':row_id', $row_id);    // Changed from member_id
        $stmt->bindParam(':tree_id', $tree_id);
        $stmt->bindParam(':tag_type', $tag_type);
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    public function deleteTag($delTag) {
        // All fields are required - change member_id to row_id
        $tree_id = intval($delTag['tree_id']);
        $row_id = intval($delTag['row_id']);     // Changed from member_id
        $tag = trim($delTag['tag']);
        $tag_type = $delTag['tag_type'];

        if (!$tree_id || !$row_id || !$tag || !$tag_type) {
            return false;
        }

        $query = "DELETE FROM $this->notes_table 
                  WHERE tag = :tag_name 
                  AND tree_id = :tree_id 
                  AND row_id = :row_id           
                  AND tag_type = :tag_type";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tag_name', $tag);
        $stmt->bindParam(':row_id', $row_id);    // Changed from member_id
        $stmt->bindParam(':tree_id', $tree_id);
        $stmt->bindParam(':tag_type', $tag_type);
        return $stmt->execute();
    }
}
