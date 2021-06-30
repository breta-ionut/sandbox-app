<script src="./Login.js"></script>

<template>
    <div class="flex justify-center py-16">
        <form class="px-8 py-4 bg-white border rounded shadow-md" @submit.prevent="login">
            <div class="mt-2 mb-6 p-2 bg-red-100 border border-red-500 rounded text-sm text-red-500 text-center" v-if="formErrors.global.length">
                <p v-for="formError in formErrors.global">{{ formError }}</p>
            </div>

            <div class="mb-4">
                <label for="username" class="block mb-2 text-md text-gray-700 font-bold">Email</label>

                <input type="email" name="username" id="username" class="px-3 py-2 border rounded shadow text-gray-600" :class="{'border-red-500': formErrors.fields.username}" placeholder="john.doe@example.com" required v-model="credentials.username" />

                <p class="mt-3 text-sm text-red-500" v-for="formError in formErrors.fields.username">{{ formError }}</p>
            </div>

            <div>
                <label for="password" class="block mb-2 text-md text-gray-700 font-bold">Password</label>

                <input type="password" name="password" id="password" class="px-3 py-2 border rounded shadow text-gray-600" :class="{'border-red-500': formErrors.fields.password}" placeholder="********" required v-model="credentials.password" />

                <p class="mt-3 text-sm text-red-500" v-for="formError in formErrors.fields.password">{{ formError }}</p>
            </div>

            <div class="mt-8">
                <input type="submit" name="submit" value="Login" class="px-6 py-2 bg-blue-400 hover:bg-blue-600 rounded text-white font-bold cursor-pointer" />
            </div>
        </form>
    </div>
</template>
