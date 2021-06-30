import {mapState} from 'vuex'

export default {
    computed: mapState('user', {
        /**
         * @return {boolean}
         */
        userLoading: state => !state.userLoaded,
    }),
}
