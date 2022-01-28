const {SlashCommandBuilder} = require('@discordjs/builders');
const needle = require('needle');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('deff-call')
        .setDescription('Creates a new Deff-Call')
        .addStringOption(option =>
            option.setName('alliance')
                .setDescription('The id of the alliance')
                .setRequired(true))
        .addStringOption(option =>
            option.setName('arrival')
                .setDescription('The time deff has to arrive')
                .setRequired(true))
        .addStringOption(option =>
            option.setName('player')
                .setDescription('The player to deff')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('x')
                .setDescription('The x-Coordinate the deff-call is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('y')
                .setDescription('The y-Coordinate the deff-call is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('grain')
                .setDescription('The current grain the deff has')
                .setRequired(false))
        .addIntegerOption(option =>
            option.setName('grain-storage')
                .setDescription('The maximum grain the deff has')
                .setRequired(false))
        .addIntegerOption(option =>
            option.setName('grain-production')
                .setDescription('The maximum grain the deff has')
                .setRequired(false))
        .addBooleanOption(option =>
            option.setName('advanced')
                .setDescription('Use advanced features.')
                .setRequired(false))
        .addIntegerOption(option =>
            option.setName('troop-ratio')
                .setDescription('Percent value of anti-infantry deff.')
                .setRequired(false)),
    async execute(interaction) {
        needle(
            'post',
            'http://travian.idrinth.de/api/deff-call',
            'alliance=' + interaction.options.getString('alliance')
                + '&arrival=' + interaction.options.getString('arrival')
                + '&x=' + interaction.options.getInteger('x')
                + '&y=' + interaction.options.getInteger('y')
                + '&grain=' + interaction.options.getInteger('grain', 0)
                + '&grain-storage=' + interaction.options.getInteger('grain-storage', 0)
                + '&grain-production=' + interaction.options.getInteger('grain-production', 0)
                + '&advanced-troop-data=' + interaction.options.getBoolean('advanced', 0)
                + '&troop-ratio=' + interaction.options.getInteger('troop-ratio', 50),
            {'X-API-KEY': process.env.API_KEY}
        )
            .then(async function(resp) {
                const id = resp.id;
                const key = resp.key;
                await interaction.reply(`@everyone Deff-Call: https://travian.idrinth.de/deff-call/${id}`);
                await interaction.followUp({content: `https://travian.idrinth.de/deff-call/${id}/${key}`, ephemeral: true});
            })
            .catch(function(err) {
                interaction.followUp({content: 'Failed creating Deff-Call: ' + err, ephemeral: true});
           });
    },
};
