class DescendantsController {
    private $visitedMembers = [];

    public function getDescendantsData($memberId) {
        $this->visitedMembers = [];
        return $this->getMemberWithDescendants($memberId);
    }

    private function getMemberWithDescendants($memberId) {
        // Prevent infinite loops
        if (in_array($memberId, $this->visitedMembers)) {
            return null;
        }
        $this->visitedMembers[] = $memberId;

        $member = $this->memberModel->getMember($memberId);
        if (!$member) return null;

        $data = [
            'id' => $memberId,
            'name' => $member['first_name'] . ' ' . $member['last_name'],
            'data' => [
                'gender' => $member['gender'],
                'birth_date' => $member['birth_date']
            ],
            'marriages' => []
        ];

        $marriages = $this->marriageModel->getMemberMarriages($memberId);
        foreach ($marriages as $marriage) {
            $spouse = null;
            $children = [];
            
            if ($marriage['spouse_id']) {
                $spouseData = $this->memberModel->getMember($marriage['spouse_id']);
                if ($spouseData) {
                    $spouse = [
                        'id' => $marriage['spouse_id'],
                        'name' => $spouseData['first_name'] . ' ' . $spouseData['last_name'],
                        'data' => ['gender' => $spouseData['gender']]
                    ];
                }
            }

            $marriageChildren = $this->memberModel->getChildrenByMarriage($marriage['id']);
            foreach ($marriageChildren as $child) {
                $childData = $this->getMemberWithDescendants($child['id']);
                if ($childData) {
                    $children[] = $childData;
                }
            }

            $data['marriages'][] = [
                'id' => $marriage['id'],
                'spouse' => $spouse,
                'children' => $children
            ];
        }

        return $data;
    }
}
