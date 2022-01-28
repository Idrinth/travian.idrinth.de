const {SlashCommandBuilder} = require('@discordjs/builders');

module.exports = {
    data: new SlashCommandBuilder()
        .setName('deff-call')
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
                .setRequired(false)),
    async execute(interaction) {
        //require guild registration for world and alliance-locking
        await interaction.reply('Pong!');
    },
};
