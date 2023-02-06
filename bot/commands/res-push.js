const {SlashCommandBuilder} = require('@discordjs/builders');
const needle = require('needle');
const permitted = require('../permission-check');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('res-push')
        .setDescription('Creates a new Resource-Push')
        .addStringOption(option =>
            option.setName('arrival')
                .setDescription('The time resources have to arrive in YYYY-MM-DD HH:MM:SS')
                .setRequired(true))
        .addStringOption(option =>
            option.setName('player')
                .setDescription('The player to push')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('x')
                .setDescription('The x-Coordinate the village is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('y')
                .setDescription('The y-Coordinate the village is on')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('resources')
                .setDescription('The amount of tresources to send to this push')
                .setRequired(true))
        .addIntegerOption(option =>
            option.setName('grain')
                .setDescription('The amount of grain to send to this push'))
        .addIntegerOption(option =>
            option.setName('clay')
                .setDescription('The amount of clay to send to this push'))
        .addIntegerOption(option =>
            option.setName('lumber')
                .setDescription('The amount of lumber to send to this push'))
        .addIntegerOption(option =>
            option.setName('iron')
                .setDescription('The amount of iron to send to this push')),
    async execute(interaction) {
        if (!permitted(interaction, 'resource-coordinator')) {
            return interaction.reply('You don\'t have a role called Resource-Coordinator or High-Council.');
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
            'https://travian.idrinth.de/api/resource-push',
             'arrival=' + datetime
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
