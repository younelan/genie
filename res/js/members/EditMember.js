const EditMember = () => {
    const [member, setMember] = React.useState(null);
    const [loading, setLoading] = React.useState(true);
    const memberId = window.location.hash.split('/')[2];

    React.useEffect(() => {
        loadMemberData();
    }, [memberId]);

    const loadMemberData = async () => {
        try {
            const response = await fetch(`api/individuals.php?action=get&id=${memberId}`);
            const data = await response.json();
            if (data.success) {
                setMember(data.data);
            }
        } catch (error) {
            console.error('Error loading member:', error);
        } finally {
            setLoading(false);
        }
    };

    return React.createElement('div', null, [
        React.createElement(MemberDetailsForm, { 
            key: 'details',
            member: member,
            onUpdate: loadMemberData 
        }),
        React.createElement(FamilyRelationships, { 
            key: 'families',
            memberId: memberId 
        }),
        React.createElement(TagsSection, { 
            key: 'tags',
            memberId: memberId 
        })
    ]);
};
