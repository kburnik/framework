/*
    Zadatak:

    Složenost:

    Primjeri:

    Datum:

    Autor: Kristijan Burnik, udruga informatièara Božo Težak

    Gmail: kristijanburnik

*/
#include <iostream>
#include <cstdlib>
#include <algorithm>
#include <cmath>
#include <vector>
#include <set>
#include <map>
#include <queue>

#include <iostream>
#include <fstream>

using namespace std;

#ifdef _WIN32
    #include <windows.h>

    void sleep(unsigned milliseconds)
    {
        Sleep(milliseconds);
    }
#else
    #include <unistd.h>

    void sleep(unsigned milliseconds)
    {
        usleep(milliseconds * 1000); // takes microseconds
    }
#endif

int main(int argc, char *argv[]) {


    if (argc > 1 ) {
        ofstream myfile;
        myfile.open ("console.output.txt",ios::ate);
        for (int i = 1 ; i < argc; i ++)
            myfile << string(argv[i]) << " ";
        myfile << endl;
        myfile.close();
        return 0;
    } else {
        while (true) {
         string line;

         ifstream myfile;

         myfile.open("console.output.txt",ios::in);
          if (myfile.is_open())
          {
            system("cls");
            while ( myfile.good() )
            {
              getline (myfile,line);
              cout << line << endl;
             // sleep(5);
            }
            myfile.close();
            system("del console.output.txt");            
            cout << "Waiting for console output" << endl;
          }

        
    
          
          
          sleep(1000);
        }
    
    }


//    system("pause");
    return 0;
}

