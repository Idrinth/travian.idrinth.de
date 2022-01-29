const {SlashCommandBuilder} = require('@discordjs/builders');
const needle = require('needle');
const permitted = require('../permission-check');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('res-push')
        .setDescription('Creates a new Deff-Call')
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
            option.setName('resources')
                .setDescription('The amount of troops(in crop) to send to this defence')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('grain')
                .setDescription('The amount of scouts to send to this defence'))
        .addIntegerOption(option =>
            option.setName('clay')
                .setDescription('The amount of heroes to send to this defence'))
        .addIntegerOption(option =>
            option.setName('lumber')
                .setDescription('The current grain the deff has'))
        .addIntegerOption(option =>
            option.setName('iron')
                .setDescription('The maximum grain the deff has')),
    async execute(interaction) {
        if (!permitted(interaction, 'resource-coordinator')) {
            return interaction.reply('You don\'t have a role called Resource-Coordinator or High-Council.');
        }
        needle(
            'post',
            'https://travian.idrinth.de/api/resource-push',
             'arrival=' + interaction.options.getString('arrival')
                + '&x=' + interaction.options.getInteger('x')
                + '&y=' + interaction.options.getInteger('y')
                + '&player=' + interaction.options.getString('player')
                + '&resources=' + interaction.options.getInteger('resources')
                + '&grain=' + interaction.options.getInteger('grain')
                + '&clay=' + interaction.options.getInteger('clay')
                + '&lumber=' + interaction.options.getInteger('lumber')
                + '&iron=' + interaction.options.getInteger('iron')
                + '&server_id=' + interaction.guild.id
            ,
            {headers : {'X-API-KEY': process.env.API_KEY}}
        )
            .then(async function(resp) {
                if (resp.statusCode !== 200) {
                    await interaction.reply({content: 'Failed creating Res-Push: ' + resp.body.error, ephemeral: true});
                    return;
                }
                const id = resp.body.id;
                const key = resp.body.key;
                await interaction.reply(`@everyone Resource-Push: https://travian.idrinth.de/resource-push/${id}`);
                await interaction.followUp({content: `https://travian.idrinth.de/resource-push/${id}/${key}`, ephemeral: true});
            })
            .catch(function(err) {
                interaction.reply({content: 'Failed creating Res-Push: ' + err, ephemeral: true});
           });
    },
};
