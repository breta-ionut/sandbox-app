<script src="./Navbar.js"></script>

<template>
    <nav class="bg-white border-b border-gray-200 shadow">
        <div class="mx-auto px-2 sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-20">
                <div class="flex-1 flex items-center sm:items-stretch justify-center sm:justify-start">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="block w-auto h-16" src="../../images/logo.png" alt="Random Tools" />
                    </div>
                </div>

                <div class="absolute sm:static inset-y-0 sm:inset-auto right-0 flex items-center sm:ml-6 pr-2 sm:pr-0" v-click-away="hideUserMenu">
                    <div class="relative ml-3">
                        <div>
                            <button id="user-menu" class="flex bg-white rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2 focus:ring-offset-white" aria-haspopup="true" v-bind:aria-expanded="showUserMenu" @click="toggleUserMenu">
                                <span class="sr-only">Open user menu</span>
                                <span class="w-8 h-8 text-lg rounded-full fa fa-user"></span>
                            </button>
                        </div>

                        <transition enter-class="transform opacity-0 scale-95" enter-active-class="transition ease-out duration-100" enter-to-class="transform opacity-100 scale-100" leave-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-to-class="transform opacity-0 scale-95">
                            <div class="absolute right-0 w-48 mt-2 py-1 bg-white rounded-md shadow-lg origin-top-right ring-1 ring-black ring-opacity-5" role="menu" aria-orientation="vertical" aria-labelledby="user-menu" v-if="showUserMenu">
                                <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="logout">Logout</a>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
