<template>
    <nav class="bg-gray-300">
        <div class="max-w-6xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-16">
                <div class="flex-1 flex items-center sm:items-stretch justify-center sm:justify-start">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="block w-auto h-8" src="../../images/logo.png" alt="Random Tools" />
                    </div>
                </div>

                <div class="absolute sm:static inset-y-0 sm:inset-auto right-0 flex items-center sm:ml-6 pr-2 sm:pr-0">
                    <div class="relative ml-3">
                        <div>
                            <button id="user-menu" class="flex bg-gray-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-300" aria-haspopup="true">
                                <span class="sr-only">Open user menu</span>
                                <span class="w-8 h-8 rounded-full fa fa-user"></span>
                            </button>
                        </div>

                        <div class="absolute right-0 w-48 mt-2 py-1 bg-white rounded-md shadow-lg origin-top-right ring-1 ring-black ring-opacity-5" role="menu" aria-orientation="vertical" aria-labelledby="user-menu">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
