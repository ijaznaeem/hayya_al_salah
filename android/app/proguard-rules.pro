# Add project specific ProGuard rules here.
# By default, the flags in this file are appended to flags specified
# in ${sdk.dir}/tools/proguard/proguard-android.txt

# Keep Flutter framework classes
-keep class io.flutter.** { *; }
-keep class io.flutter.embedding.** { *; }

# Keep video player classes for 16 KB support
-keep class com.google.android.exoplayer2.** { *; }
-dontwarn com.google.android.exoplayer2.**

# Keep native code for 16 KB page size support
-keep class **.R$* { *; }
-keepclassmembers class **.R$* {
    public static <fields>;
}

# Keep HTTP and networking classes
-keep class okhttp3.** { *; }
-keep class retrofit2.** { *; }

# Keep JSON serialization
-keepattributes Signature
-keepattributes *Annotation*
-keepclassmembers class ** {
    @com.google.gson.annotations.SerializedName <fields>;
}

# Optimize for 16 KB page sizes
-optimizations !code/simplification/arithmetic,!code/simplification/cast,!field/*,!class/merging/*
-optimizationpasses 5
-allowaccessmodification
-dontpreverify

# Remove debug information for release builds
-renamesourcefileattribute SourceFile
-keepattributes SourceFile,LineNumberTable
