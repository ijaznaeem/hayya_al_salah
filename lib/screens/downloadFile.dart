import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';

class FileDownloader {
  static Future<void> downloadFile(
      BuildContext context, String fileUrl, String fileName) async {
    CancelToken cancelToken = CancelToken();
    try {
      // Request permission for Android 10+ and higher
      if (!await Permission.manageExternalStorage.isGranted) {
        await Permission.manageExternalStorage.request();
      }
      if (!await Permission.storage.isGranted) {
        await Permission.storage.request();
      }

      // Get the Downloads directory path for Android 10+ and higher
      final directory = Directory('/storage/emulated/0/Download');
      final filePath = '${directory.path}/$fileName';

      // Download the PDF file using Dio
      Dio dio = Dio();
      await dio.download(
        fileUrl,
        filePath,
        cancelToken: cancelToken,
        onReceiveProgress: (received, total) {
          if (total != -1) {
            double progress = (received / total) * 100;
            debugPrint('Downloading: $progress%');
            showDialog(
              context: context,
              barrierDismissible: false,
              builder: (BuildContext context) {
                return AlertDialog(
                  title: const Text('Downloading'),
                  content: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      LinearProgressIndicator(value: received / total),
                      const SizedBox(height: 20),
                      Text('${(received / total * 100).toStringAsFixed(0)}%'),
                      const SizedBox(height: 20),
                      ElevatedButton(
                        onPressed: () {
                          cancelToken.cancel();
                          Navigator.of(context, rootNavigator: true)
                              .pop(); // Dismiss the dialog
                        },
                        child: const Text('Cancel'),
                      ),
                    ],
                  ),
                );
              },
            );
          }
        },
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('File saved to $filePath')),
      );
    } on DioException catch (e) {
      if (CancelToken.isCancel(e)) {
        debugPrint('Download canceled');
        Navigator.of(context, rootNavigator: true).pop(); // Dismiss the dialog
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Download canceled')),
        );
      } else {
        debugPrint('Download error: $e');
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to download file!')),
        );
      }
    }
  }
}
