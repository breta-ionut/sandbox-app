<script src="./Login.js"></script>

<template>
    <div class="flex justify-center py-16">
        <form class="px-8 py-4 bg-white border rounded shadow-md">
            <div class="mb-4">
                <label for="email" class="block mb-2 text-md text-gray-700 font-bold">Email</label>

                <input type="text" name="email" id="email" class="px-3 py-2 border rounded shadow text-gray-600" placeholder="john.doe@example.com" />
            </div>

            <div>
                <label for="password" class="block mb-2 text-md text-gray-700 font-bold">Password</label>

                <input type="password" name="password" id="password" class="px-3 py-2 border rounded shadow text-gray-600" placeholder="********" />
            </div>

            <div class="mt-8">
                <input type="submit" name="submit" value="Login" class="px-6 py-2 bg-blue-400 hover:bg-blue-600 rounded text-white font-bold cursor-pointer" />
            </div>
        </form>
    </div>
</template>
