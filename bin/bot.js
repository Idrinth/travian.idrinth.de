require('dotenv').config();
const fs = require('fs');
const { Client, Collection, Intents } = require('discord.js');
const { REST } = require('@discordjs/rest');
const { Routes } = require('discord-api-types/v9');
const commands = [];
const client = new Client({ intents: [Intents.FLAGS.GUILDS] });
client.commands = new Collection();

for (const file of fs.readdirSync(__dirname + '/../bot/commands').filter(file => file.endsWith('.js'))) {
    const command = require(`${__dirname}/../bot/commands/${file}`);
    client.commands.set(command.data.name, command);
    commands.push(command.data.toJSON());
}
for (const file of fs.readdirSync(__dirname + '/../bot/events').filter(file => file.endsWith('.js'))) {
    const event = require(`${__dirname}/../bot/events/${file}`);
    if (event.once) {
        client.once(event.name, (...args) => event.execute(client, ...args));
    } else {
        client.on(event.name, (...args) => event.execute(client, ...args));
    }
}

const rest = new REST({ version: '9' }).setToken(process.env.DISCORD_BOT_TOKEN);
/*rest.put(Routes.applicationCommands(process.env.DISCORD_CLIENT_ID), { body: commands })
    .then(() => console.log('Successfully registered application commands.'))
    .catch(console.error);*/
rest.put(Routes.applicationGuildCommands(process.env.DISCORD_CLIENT_ID, process.env.DISCORD_GUILD_ID), { body: commands })
    .then(() => console.log('Successfully registered guild application commands.'))
    .catch(console.error);

client.login(process.env.DISCORD_BOT_TOKEN);