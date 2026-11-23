import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:barber_flow/main.dart';
import 'package:barber_flow/pages/lupa_password_page.dart';
import 'package:barber_flow/pages/ubah_password_page.dart';

void main() {
  setUp(() => SharedPreferences.setMockInitialValues({}));

  testWidgets('Welcome page tampil', (WidgetTester tester) async {
    await tester.pumpWidget(const BarberFlowApp());

    expect(find.text('REGISTRASI'), findsOneWidget);
    expect(find.text('LOGIN'), findsOneWidget);
  });

  testWidgets('Lupa password: hanya meminta email, validasi email kosong/salah', (WidgetTester tester) async {
    await tester.pumpWidget(const MaterialApp(home: LupaPasswordPage()));
    await tester.pump();

    expect(find.text('Email'), findsOneWidget);
    expect(find.text('Password baru'), findsNothing); // tidak ada kolom password di langkah ini

    await tester.tap(find.text('AJUKAN RESET PASSWORD'));
    await tester.pump();
    expect(find.text('Email wajib diisi'), findsOneWidget);

    await tester.enterText(find.byType(TextFormField), 'bukan-email');
    await tester.tap(find.text('AJUKAN RESET PASSWORD'));
    await tester.pump();
    expect(find.text('Email tidak valid'), findsOneWidget);
  });

  testWidgets('Ganti password sementara: wajib, validasi kolom', (WidgetTester tester) async {
    await tester.pumpWidget(const MaterialApp(home: UbahPasswordPage(wajib: true)));

    expect(find.text('Buat Password Baru'), findsOneWidget);
    expect(find.text('Password sementara'), findsOneWidget);

    await tester.enterText(find.byType(TextFormField).at(0), 'Sementara1');
    await tester.enterText(find.byType(TextFormField).at(1), 'baru12345');
    await tester.enterText(find.byType(TextFormField).at(2), 'beda12345');
    await tester.tap(find.text('SIMPAN PASSWORD'));
    await tester.pump();
    expect(find.text('Password tidak sama'), findsOneWidget);
  });
}
