const YAML = require('yaml');
const fs = require('fs');
const file = fs.readFileSync(__dirname + '/../../translations/en.yml', 'utf8');
const translations = YAML.parse(file);

module.exports = {
    name: 'interactionCreate',
    execute(client, interaction) {
        console.log(`${interaction.user.tag} in #${interaction.channel?.name} triggered an interaction.`);
        if (!interaction.isCommand()) {
            return;
        }
        const command = client.commands.get(interaction.commandName);
        if (!command) {
            return;
        }
        try {
            command.execute(interaction, translations);
        } catch (error) {
            console.error(error);
            interaction.reply({ content: 'There was an error while executing this command!', ephemeral: true });
        }
    },
};