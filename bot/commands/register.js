const {SlashCommandBuilder} = require('@discordjs/builders');
const needle = require('needle');

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
        if (interaction.guild.ownerId != interaction.member.id) {
            await interaction.reply({content: 'You need to be the server\'s owner for this command.', ephemeral: true});
            return;
        }
        needle(
            'post',
            'https://travian.idrinth.de/api/register',
            'id=' + interaction.options.getString('alliance')
                + '&key=' + interaction.options.getString('key')
                + '&server_id=' + interaction.guild.id
            ,
            {headers : {'X-API-KEY': process.env.API_KEY}}
        )
            .then(async function(resp) {
                if (resp.statusCode !== 200) {
                    await interaction.reply({content: 'Failed registration: ' + resp.body.error, ephemeral: true});
                    return;
                }
                await interaction.reply({content: 'Registered your server.', ephemeral: true});
            })
            .catch(async function(err) {
                await interaction.reply({content: 'Failed registration: ' + err, ephemeral: true});
           });
    },
};
