import {mapState} from 'vuex'

export default {
    computed: mapState('user', {
        userLoading: state => !state.userLoaded,
    }),
}
