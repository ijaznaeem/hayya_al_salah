# Hayya Al Salah v1.2.4 - Release Notes

## Critical Play Store Compliance Fix

**Version:** 1.2.4 (Build 17)  
**Release Date:** September 25, 2025  
**Status:** Play Store Compliance Release

## 🎯 Primary Fix

**Removed Media Permissions Issue:**
- **Identified and removed** the `open_filex` plugin which was automatically adding `READ_MEDIA_IMAGES` and `READ_MEDIA_VIDEO` permissions
- These permissions were causing Play Store rejections due to policy violations
- The plugin was unused in the codebase, making it safe to remove

## 🔧 Technical Changes

### Dependencies Removed:
- ✅ `open_filex: ^4.6.0` - **Root cause of media permissions**
- ✅ `flutter_downloader: ^1.11.8` - Unused dependency  
- ✅ `permission_handler: ^11.3.1` - Unused dependency

### Build Configuration:
- ✅ Fixed deprecated `FlutterMultiDexApplication` reference
- ✅ Updated Android manifest to modern Flutter application setup
- ✅ Removed all commented media permissions from manifest

### Backend Enhancements:
- ✅ Added absolute URL processing for lecture videos, thumbnails, and documents
- ✅ Updated all lecture and topic endpoints to return absolute URLs
- ✅ Enhanced error handling and response formatting

## 📱 App Bundle Details

- **File:** `app-release.aab` (30.4MB)
- **Target SDK:** Android 15+ with 16KB page size support
- **Permissions:** Only `INTERNET` permission (Play Store compliant)
- **Features:** Tree-shaken fonts (99.4-99.5% reduction)

## 🛡️ Play Store Compliance

This release specifically addresses:
- ❌ **Previous Issue:** "READ_MEDIA_IMAGES/READ_MEDIA_VIDEO permissions not compliant"
- ✅ **Resolution:** Completely removed all media permissions and unused plugins
- ✅ **Validation:** Built successfully without any media permission dependencies

## 🚀 Ready for Play Store Submission

The app is now fully compliant with Google Play Developer Programme Policies and ready for resubmission to the Play Store.

### Key Compliance Points:
1. ✅ No media permissions requested
2. ✅ No deprecated Android classes
3. ✅ Modern Flutter application structure  
4. ✅ Android 15+ compatibility
5. ✅ COPPA compliance for children's safety
6. ✅ Privacy policy integration

---

## For Development Team

**Next Steps:**
1. Submit `app-release.aab` (build 17) to Play Store
2. Update Play Store listing with v1.2.4 features
3. Monitor Play Store review process
4. Prepare for production release once approved

**Previous Rejection Reasons - Now Fixed:**
- ❌ Media permissions policy violation → ✅ All media permissions removed
- ❌ Deprecated FlutterMultiDexApplication → ✅ Updated to modern Flutter app
- ❌ Broken functionality → ✅ Clean build with proper dependencies
