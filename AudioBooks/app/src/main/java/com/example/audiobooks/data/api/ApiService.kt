package com.example.audiobooks.data.api

import com.example.audiobooks.data.models.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // Авторизация
    @POST("login.php")
    suspend fun login(@Body request: LoginRequest): Response<ApiResponse<User>>

    @POST("register.php")
    suspend fun register(@Body request: RegisterRequest): Response<ApiResponse<User>>

    // Книги
    @GET("books.php")
    suspend fun getBooks(
        @Query("genre") genre: String? = null,
        @Query("search") search: String? = null,
        @Query("page") page: Int = 1,
        @Query("user_id") userId: Int
    ): Response<ApiResponse<BooksResponse>>

    @GET("book_detail.php")
    suspend fun getBookDetail(
        @Query("id") bookId: Int,
        @Query("user_id") userId: Int
    ): Response<ApiResponse<BookDetail>>

    // Жанры
    @GET("genres.php")
    suspend fun getGenres(): Response<ApiResponse<GenresResponse>>

    // Избранное
    @GET("wishlist.php")
    suspend fun getWishlist(
        @Query("user_id") userId: Int
    ): Response<ApiResponse<WishlistBooksResponse>>

    @POST("wishlist.php")
    suspend fun toggleWishlist(@Body request: WishlistRequest): Response<ApiResponse<Any>>

    // Оценка
    @POST("rate.php")
    suspend fun rateBook(@Body request: RateRequest): Response<ApiResponse<RatingResponse>>
}