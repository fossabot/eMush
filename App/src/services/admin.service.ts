import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import store from "@/store";

// @ts-ignore
const ADMIN_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "admin");
// @ts-ignore
const PLAYER_INFO_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "player_infos");
// @ts-ignore
const QUARANTINE_PLAYER_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "player/quarantine");

const AdminService = {
    getPlayerInfoList: async(params: Record<string, unknown> | undefined): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.get(PLAYER_INFO_ENDPOINT, params);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    putUserOutOfGame: async(userId: string): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.patch(urlJoin(ADMIN_ENDPOINT, userId, 'put-out-of-game'));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },
    quarantinePlayer: async(playerId: number): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(QUARANTINE_PLAYER_ENDPOINT + '/' + playerId);
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    },

};
export default AdminService;
