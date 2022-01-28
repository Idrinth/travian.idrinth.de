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
                .setDescription('The current grain the deff has'))
        .addIntegerOption(option =>
            option.setName('grain-storage')
                .setDescription('The maximum grain the deff has'))
        .addIntegerOption(option =>
            option.setName('grain-production')
                .setDescription('The maximum grain the deff has'))
        .addBooleanOption(option =>
            option.setName('advanced')
                .setDescription('Use advanced features.'))
        .addIntegerOption(option =>
            option.setName('troop-ratio')
                .setDescription('Percent value of anti-infantry deff.')),
    async execute(interaction) {
        needle(
            'post',
            'https://travian.idrinth.de/api/deff-call',
            'alliance=' + interaction.options.getString('alliance')
                + '&arrival=' + interaction.options.getString('arrival')
                + '&x=' + interaction.options.getInteger('x')
                + '&y=' + interaction.options.getInteger('y')
                + '&grain=' + interaction.options.getInteger('grain')
                + '&grain-storage=' + interaction.options.getInteger('grain-storage')
                + '&grain-production=' + interaction.options.getInteger('grain-production')
                + '&advanced-troop-data=' + interaction.options.getBoolean('advanced')
                + '&troop-ratio=' + interaction.options.getInteger('troop-ratio')
            ,
            {'X-API-KEY': process.env.API_KEY}
        )
            .then(async function(resp) {
                const id = resp.body.id;
                const key = resp.body.key;
                await interaction.reply(`@everyone Deff-Call: https://travian.idrinth.de/deff-call/${id}`);
                await interaction.followUp({content: `https://travian.idrinth.de/deff-call/${id}/${key}`, ephemeral: true});
            })
            .catch(function(err) {
                interaction.followUp({content: 'Failed creating Deff-Call: ' + err, ephemeral: true});
           });
    },
};
