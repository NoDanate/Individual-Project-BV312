package com.example.audiobooks.data.api

import android.content.Context
import android.util.Log
import com.example.audiobooks.utils.SessionManager
import com.google.gson.GsonBuilder
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit
//Настройка работы с API через Retrofit
object RetrofitClient {
    // Ссылка на API сервера

    private const val BASE_URL = "http://192.168.1.47/api/"
    //private const val BASE_URL = "http://172.20.10.2/api/"

    private var sessionManager: SessionManager? = null
    // Инициализация менеджера сессий (авторизован ли пользователь)
    fun init(context: Context) {
        sessionManager = SessionManager(context)
    }
    // Получение id авторизованного пользователя (для работы с запросами API)
    fun getUserId(): Int {
        return sessionManager?.getUserId() ?: 0
    }
    // Логирование HTTP-запросов и ответов в лог
    private val loggingInterceptor = HttpLoggingInterceptor { message ->
        Log.d("API_DEBUG", message)
    }.apply {
        level = HttpLoggingInterceptor.Level.BODY
    }
    // Настройка клиеннта с расчетом таймаутов
    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor(loggingInterceptor)
        .connectTimeout(30, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .writeTimeout(30, TimeUnit.SECONDS)
        .build()
    // Настройка гибкой обработки JSON
    private val gson = GsonBuilder()
        .setLenient()
        .create()
    // Инициализация API-сервиса
    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .build()
            .create(ApiService::class.java)
    }
}