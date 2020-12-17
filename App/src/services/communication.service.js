import ApiService from "@/services/api.service";
import {Channel} from "@/entities/Channel";
import {Message} from "@/entities/Message";

const CHANNELS_ENDPOINT = process.env.VUE_APP_API_URL+'channel'

const CommunicationService = {

    loadChannels: async() => {
        const channelsData = await ApiService.get(CHANNELS_ENDPOINT)

        let channels = [];
        if (channelsData.data) {
            channelsData.data.forEach((data) => {
                channels.push((new Channel()).load(data));
            })
        }
        return channels;
    },

    createPrivateChannel: async () => {
        const channelsData = await ApiService.post(CHANNELS_ENDPOINT)

        let channels = [];
        if (channelsData.data) {
            channelsData.data.forEach((data) => {
                channels.push((new Channel()).load(data));
            })
        }
        return channels;
    },

    loadMessages: async (channel) => {
        const messagesData = await ApiService.get(CHANNELS_ENDPOINT + '/' + channel.id + '/message')

        let messages = [];
        if (messagesData.data) {
            messagesData.data.forEach((data) => {
                messages.push((new Message()).load(data));
            })
        }
        return messages;
    },

    sendMessage: async (channel, text, parent) => {

        let parentId = null;
        if (typeof parent !== "undefined") {
            parentId = parent.id;
        }

        const messagesData = await ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/message', {
            'message': text,
            'parent': parentId
        })
        console.log(messagesData)
        let messages = [];
        if (messagesData.data) {
            messagesData.data.forEach((data) => {
                messages.push((new Message()).load(data));
            })
        }
        return messages;
    }
}
export default CommunicationService