const FamiliesAPI = {
    async getFamilies(memberId) {
        const response = await fetch(`api/families.php?action=list&member_id=${memberId}`);
        return await response.json();
    },

    async createFamily(data) {
        const response = await fetch('api/families.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'create', ...data })
        });
        return await response.json();
    },

    async addChild(familyId, childData) {
        const response = await fetch('api/families.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'addChild', familyId, ...childData })
        });
        return await response.json();
    },

    async addSpouse(familyId, spouseData) {
        const response = await fetch('api/families.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'addSpouse', familyId, ...spouseData })
        });
        return await response.json();
    },

    async deleteFamily(familyId) {
        const response = await fetch(`api/families.php?id=${familyId}`, {
            method: 'DELETE'
        });
        return await response.json();
    }
};
