const {SlashCommandBuilder} = require('@discordjs/builders');
const needle = require('needle');
const permitted = require('../permission-check');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('deff-call')
        .setDescription('Creates a new Deff-Call')
        .addStringOption(option =>
            option.setName('arrival')
                .setDescription('The time deff has to arrive in YYYY-MM-DD HH:MM:SS')
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
            option.setName('troops')
                .setDescription('The amount of troops(in crop) to send to this defence')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('scouts')
                .setDescription('The amount of scouts to send to this defence'))
        .addIntegerOption(option =>
            option.setName('heroes')
                .setDescription('The amount of heroes to send to this defence'))
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
        if (!permitted(interaction, 'defence-coordinator')) {
            return interaction.reply('You don\'t have a role called Defence-Coordinator or High-Council.');
        }
        let datetime = interaction.options.getString('arrival');
        let matches;
        if (matches = datetime.match(/^([0-9]+):[0-9]+(:[0-9]+)?$/)) {
            const now = new Date();
            if (Number.parseInt(matches[1], 10) > now.getUTCHours()+1) {
                datetime = `${now.getUTCFullYear()}-${now.getUTCMonth() +1}-${now.getUTCDate()} ${datetime}`;
            } else {
                const tomorrow = new Date(now.valueOf()+86400000);
                datetime = `${tomorrow.getUTCFullYear()}-${tomorrow.getUTCMonth() +1}-${tomorrow.getUTCDate()} ${datetime}`;
            }
        }
        needle(
            'post',
            'https://travian.idrinth.de/api/deff-call',
             'arrival=' + datetime
                + '&x=' + interaction.options.getInteger('x')
                + '&y=' + interaction.options.getInteger('y')
                + '&player=' + interaction.options.getString('player')
                + '&grain=' + interaction.options.getInteger('grain')
                + '&grain-storage=' + interaction.options.getInteger('grain-storage')
                + '&grain-production=' + interaction.options.getInteger('grain-production')
                + '&advanced-troop-data=' + (interaction.options.getBoolean('advanced')?1:0)
                + '&troop-ratio=' + interaction.options.getInteger('troop-ratio')
                + '&scouts=' + interaction.options.getInteger('scouts')
                + '&heroes=' + interaction.options.getInteger('heroes')
                + '&troops=' + interaction.options.getInteger('troops')
                + '&server_id=' + interaction.guild.id
            ,
            {headers : {'X-API-KEY': process.env.API_KEY}}
        )
            .then(async function(resp) {
                if (resp.statusCode !== 200) {
                    await interaction.reply({content: 'Failed creating Deff-Call: ' + resp.body.error, ephemeral: true});
                    return;
                }
                const id = resp.body.id;
                const key = resp.body.key;
                await interaction.reply(`@everyone Deff-Call: https://travian.idrinth.de/deff-call/${id}`);
                await interaction.followUp({content: `https://travian.idrinth.de/deff-call/${id}/${key}`, ephemeral: true});
            })
            .catch(function(err) {
                interaction.reply({content: 'Failed creating Deff-Call: ' + err, ephemeral: true});
           });
    },
};
