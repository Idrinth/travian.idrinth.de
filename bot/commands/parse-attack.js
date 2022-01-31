const {SlashCommandBuilder} = require('@discordjs/builders');
const needle = require('needle');
const permitted = require('../permission-check');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('parse-attack')
        .setDescription('Analyze attack')
        .addStringOption(option =>
            option.setName('duration')
                .setDescription('The remaining time')
                .setRequired(true))
        .addStringOption(option =>
            option.setName('blind-time')
                .setDescription('The time the attack may have gone unnoticed')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('from-x')
                .setDescription('The x-Coordinate the village is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('from-y')
                .setDescription('The y-Coordinate the village is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('to-x')
                .setDescription('The x-Coordinate the village is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('to-y')
                .setDescription('The y-Coordinate the village is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('speed')
                .setDescription('The world speed, defaults to 1')),
    async execute(interaction) {
        needle(
            'post',
            'https://travian.idrinth.de/api/parse-attack',
             'duration=' + interaction.options.getString('duration')
                + '&blind_time=' + interaction.options.getString('blind_time')
                + '&fromX=' + interaction.options.getInteger('from-x')
                + '&fromY=' + interaction.options.getInteger('from-y')
                + '&toX=' + interaction.options.getInteger('to-x')
                + '&toY=' + interaction.options.getInteger('to-y')
                + '&speed=' + interaction.options.getInteger('speed')
            ,
            {headers : {'X-API-KEY': process.env.API_KEY}}
        )
            .then(async function(resp) {
                if (resp.statusCode !== 200) {
                    await interaction.reply({content: 'Failed analyzing: ' + resp.body.error, ephemeral: true});
                    return;
                }
                await interaction.reply(
                    `Attack (${interaction.options.getInteger('from-x')}|${interaction.options.getInteger('from-y')}) -> (${interaction.options.getInteger('to-x')}|${interaction.options.getInteger('to-y')})`
                );
                if (resp.body.length === 0) {
                    await interaction.followUp(
                        `nothing matching!`
                    );
                } 
                for (const item of resp.body) {
                    await interaction.followUp(
                        `${item.start}->X->${item.returned} Speed ${item.speed} TS ${item.tournament_square}: ${item.units.join(',')}`
                    );
                }
            })
            .catch(function(err) {
                interaction.reply({content: 'Failed analyzing: ' + err, ephemeral: true});
           });
    },
};
