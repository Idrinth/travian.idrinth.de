module.exports = function (interaction, roleName) {
    if (interaction.guild.ownerId === interaction.member.id) {
        return true;
    }
    if (interaction.member?.roles?.cache?.some(role => role.name.toLowerCase() === 'high-council')) {
        return true;
    }
    if (interaction.member?.roles?.cache?.some(role => role.name.toLowerCase() === roleName)) {
        return true;
    }
    return false;
}