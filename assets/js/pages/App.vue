<style src="../../css/index.css"></style>

<script src="./App.js"></script>

<template>
    <router-view></router-view>

    <div class="flex justify-center pt-40" v-if="userLoading">
        <div class="main-loader"></div>
    </div>
</template>
