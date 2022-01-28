const {SlashCommandBuilder} = require('@discordjs/builders');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('register')
        .setDescription('Register a discord for a specific alliance')
        .addStringOption(option =>
            option.setName('alliance')
                .setDescription('The id of the alliance (see in url)')
                .setRequired(true))
        .addStringOption(option =>
            option.setName('key')
                .setDescription('The key of the alliance (see in invite-url)')
                .setRequired(true)),
    async execute(interaction) {
        await interaction.reply({content: 'Pong!', ephemeral: true});
    },
};
