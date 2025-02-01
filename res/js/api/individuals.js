const IndividualsAPI = {
    async getMembers(treeId, page = 1, limit = 70) {
        const response = await fetch(`api/individuals.php?action=list&tree_id=${treeId}&page=${page}&limit=${limit}`);
        return await response.json();
    },

    async getMemberDetails(memberId) {
        const response = await fetch(`api/individuals.php?action=details&id=${memberId}`);
        return await response.json();
    },

    async createMember(memberData) {
        try {
            const response = await fetch('api/individuals.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(memberData)
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Failed to create member');
            }
            return data;
        } catch (error) {
            throw new Error(error.message || 'Network error occurred');
        }
    },

    async updateMember(memberId, memberData) {
        const response = await fetch(`api/individuals.php?id=${memberId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(memberData)
        });
        return await response.json();
    },

    async deleteMember(memberId) {
        const response = await fetch(`api/individuals.php?id=${memberId}`, {
            method: 'DELETE'
        });
        return await response.json();
    },

    async searchMembers(treeId, query) {
        const response = await fetch(`api/individuals.php?action=search&tree_id=${treeId}&query=${query}`);
        return await response.json();
    }
};
